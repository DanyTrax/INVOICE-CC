<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GoogleDriveService
{
    protected $settings;
    protected $accessToken;
    protected $baseUrl = 'https://www.googleapis.com/drive/v3';

    public function __construct()
    {
        $this->settings = app(GeneralSettings::class);
    }

    /**
     * Obtener token de acceso usando Service Account
     */
    protected function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $serviceAccountJson = $this->settings->drive_service_account_json;
        
        if (empty($serviceAccountJson)) {
            throw new \Exception('Google Drive no está configurado. Por favor, configura el JSON de Service Account en Configuración.');
        }

        try {
            $serviceAccount = json_decode($serviceAccountJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('El JSON de Service Account no es válido.');
            }

            // Crear JWT para obtener token
            $jwt = $this->createJWT($serviceAccount);
            
            // Solicitar token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('Error al obtener token de Google Drive', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                throw new \Exception('Error al obtener token de acceso de Google Drive: ' . $response->body());
            }

            $data = $response->json();
            $this->accessToken = $data['access_token'] ?? null;

            if (!$this->accessToken) {
                throw new \Exception('No se pudo obtener el token de acceso.');
            }

            return $this->accessToken;
        } catch (\Exception $e) {
            Log::error('Error en GoogleDriveService::getAccessToken', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Crear JWT para autenticación
     */
    protected function createJWT(array $serviceAccount)
    {
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $signatureInput = $headerEncoded . '.' . $payloadEncoded;

        // Firmar con la clave privada
        $privateKey = $serviceAccount['private_key'];
        openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        
        if (!$signature) {
            throw new \Exception('Error al firmar el JWT.');
        }

        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Crear carpeta en Google Drive
     */
    public function createFolder($folderName, $parentFolderId = null)
    {
        try {
            $token = $this->getAccessToken();

            // Si no se especifica carpeta padre, usar la configurada en settings
            if (!$parentFolderId) {
                $parentFolderId = $this->settings->drive_folder_id;
            }

            $metadata = [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ];

            if ($parentFolderId) {
                $metadata['parents'] = [$parentFolderId];
            }

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/files', [
                    'json' => $metadata,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Error al crear carpeta en Google Drive', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'folderName' => $folderName,
                ]);
                throw new \Exception('Error al crear carpeta en Google Drive: ' . $response->body());
            }

            $folder = $response->json();
            
            return [
                'id' => $folder['id'],
                'name' => $folder['name'],
                'webViewLink' => 'https://drive.google.com/drive/folders/' . $folder['id'],
            ];
        } catch (\Exception $e) {
            Log::error('Error en GoogleDriveService::createFolder', [
                'message' => $e->getMessage(),
                'folderName' => $folderName,
            ]);
            throw $e;
        }
    }

    /**
     * Subir archivo a Google Drive
     */
    public function uploadFile($filePath, $fileName, $parentFolderId, $mimeType = null)
    {
        try {
            $token = $this->getAccessToken();

            // Detectar MIME type si no se proporciona
            if (!$mimeType) {
                $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
            }

            // Primero crear metadata del archivo
            $metadata = [
                'name' => $fileName,
                'parents' => [$parentFolderId],
            ];

            // Subir archivo
            $response = Http::withToken($token)
                ->attach('metadata', json_encode($metadata), 'application/json')
                ->attach('file', file_get_contents($filePath), $fileName)
                ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', [
                    'headers' => [
                        'Content-Type' => 'multipart/related',
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Error al subir archivo a Google Drive', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'fileName' => $fileName,
                ]);
                throw new \Exception('Error al subir archivo a Google Drive: ' . $response->body());
            }

            $file = $response->json();
            
            return [
                'id' => $file['id'],
                'name' => $file['name'],
                'webViewLink' => 'https://drive.google.com/file/d/' . $file['id'] . '/view',
                'webContentLink' => $file['webContentLink'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Error en GoogleDriveService::uploadFile', [
                'message' => $e->getMessage(),
                'fileName' => $fileName,
            ]);
            throw $e;
        }
    }

    /**
     * Mover archivo de una carpeta a otra
     */
    public function moveFile($fileId, $newParentFolderId, $oldParentFolderId = null)
    {
        try {
            $token = $this->getAccessToken();

            // Primero obtener los padres actuales si no se proporciona
            if (!$oldParentFolderId) {
                $fileResponse = Http::withToken($token)
                    ->get($this->baseUrl . '/files/' . $fileId . '?fields=parents');
                
                if ($fileResponse->successful()) {
                    $fileData = $fileResponse->json();
                    $oldParentFolderId = $fileData['parents'][0] ?? null;
                }
            }

            if ($oldParentFolderId) {
                // Remover de carpeta antigua y agregar a nueva
                $response = Http::withToken($token)
                    ->patch($this->baseUrl . '/files/' . $fileId . '?addParents=' . $newParentFolderId . '&removeParents=' . $oldParentFolderId);
            } else {
                // Solo agregar a nueva carpeta
                $response = Http::withToken($token)
                    ->patch($this->baseUrl . '/files/' . $fileId . '?addParents=' . $newParentFolderId);
            }

            if (!$response->successful()) {
                Log::error('Error al mover archivo en Google Drive', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'fileId' => $fileId,
                ]);
                throw new \Exception('Error al mover archivo en Google Drive: ' . $response->body());
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error en GoogleDriveService::moveFile', [
                'message' => $e->getMessage(),
                'fileId' => $fileId,
            ]);
            throw $e;
        }
    }

    /**
     * Mover todos los archivos de una carpeta a otra
     */
    public function moveFolderContents($sourceFolderId, $destinationFolderId)
    {
        try {
            $token = $this->getAccessToken();

            // Listar todos los archivos en la carpeta origen
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/files', [
                    'q' => "'{$sourceFolderId}' in parents and trashed=false",
                    'fields' => 'files(id, name, parents)',
                ]);

            if (!$response->successful()) {
                throw new \Exception('Error al listar archivos de la carpeta origen.');
            }

            $files = $response->json()['files'] ?? [];
            $moved = 0;

            foreach ($files as $file) {
                try {
                    $this->moveFile($file['id'], $destinationFolderId, $sourceFolderId);
                    $moved++;
                } catch (\Exception $e) {
                    Log::warning('Error al mover archivo individual', [
                        'fileId' => $file['id'],
                        'fileName' => $file['name'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $moved;
        } catch (\Exception $e) {
            Log::error('Error en GoogleDriveService::moveFolderContents', [
                'message' => $e->getMessage(),
                'sourceFolderId' => $sourceFolderId,
                'destinationFolderId' => $destinationFolderId,
            ]);
            throw $e;
        }
    }

    /**
     * Verificar conexión con Google Drive
     */
    public function testConnection()
    {
        try {
            $token = $this->getAccessToken();
            
            // Intentar listar archivos para verificar conexión
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/files', [
                    'pageSize' => 1,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
