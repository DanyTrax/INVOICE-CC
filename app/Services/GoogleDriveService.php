<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use App\Models\DriveOperationLog;
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
    public function createFolder($folderName, $parentFolderId = null, $registrationId = null, $companyId = null)
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
                ->asJson()
                ->post($this->baseUrl . '/files', $metadata, [
                    'supportsAllDrives' => 'true',
                    'fields' => 'id, name, webViewLink',
                ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                
                // Mensaje específico para API no habilitada
                if (str_contains($errorMessage, 'API has not been used') || 
                    str_contains($errorMessage, 'API not enabled') ||
                    str_contains($errorMessage, 'API activation')) {
                    $message = 'La API de Google Drive no está habilitada. Por favor, habilítala en Google Cloud Console: https://console.cloud.google.com/apis/library/drive.googleapis.com';
                } else {
                    $message = 'Error al crear carpeta en Google Drive: ' . $errorMessage;
                }
                
                Log::error('Error al crear carpeta en Google Drive', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'folderName' => $folderName,
                    'errorMessage' => $errorMessage,
                ]);
                
                throw new \Exception($message);
            }

            $folder = $response->json();
            
            $result = [
                'id' => $folder['id'],
                'name' => $folder['name'],
                'webViewLink' => 'https://drive.google.com/drive/folders/' . $folder['id'],
            ];
            
            // Registrar operación exitosa
            $this->logOperation('create_folder', 'folder', $folderName, $result['id'], $result['webViewLink'], 'success', null, [
                'parent_folder_id' => $parentFolderId,
            ], null, $registrationId, $companyId);
            
            return $result;
        } catch (\Exception $e) {
            // Registrar operación fallida
            $this->logOperation('create_folder', 'folder', $folderName, null, null, 'failed', $e->getMessage(), [
                'parent_folder_id' => $parentFolderId,
            ], null, $registrationId, $companyId);
            
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
    public function uploadFile($filePath, $fileName, $parentFolderId, $mimeType = null, $registrationId = null, $companyId = null)
    {
        try {
            $token = $this->getAccessToken();

            // Detectar MIME type si no se proporciona
            if (!$mimeType) {
                // Intentar usar finfo si está disponible
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $filePath);
                    finfo_close($finfo);
                }
                
                // Si aún no tenemos MIME type, intentar por extensión
                if (!$mimeType || $mimeType === 'application/octet-stream') {
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $mimeType = $this->getMimeTypeByExtension($extension);
                }
                
                // Fallback final
                if (!$mimeType) {
                    $mimeType = 'application/octet-stream';
                }
            }

            // Primero crear metadata del archivo
            $metadata = [
                'name' => $fileName,
                'parents' => [$parentFolderId],
            ];

            // Subir archivo usando multipart upload
            $boundary = '----WebKitFormBoundary' . uniqid();
            $delimiter = "\r\n--{$boundary}\r\n";
            $closeDelimiter = "\r\n--{$boundary}--\r\n";
            
            $body = '';
            $body .= $delimiter;
            $body .= 'Content-Type: application/json; charset=UTF-8' . "\r\n\r\n";
            $body .= json_encode($metadata);
            $body .= $delimiter;
            $body .= 'Content-Type: ' . $mimeType . "\r\n\r\n";
            $body .= file_get_contents($filePath);
            $body .= $closeDelimiter;
            
            // Agregar parámetros para soportar Shared Drives
            $queryParams = [
                'uploadType' => 'multipart',
                'supportsAllDrives' => 'true',
                'fields' => 'id, name, webViewLink, webContentLink',
            ];
            
            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'multipart/related; boundary=' . $boundary,
                ])
                ->withBody($body, 'multipart/related; boundary=' . $boundary)
                ->post('https://www.googleapis.com/upload/drive/v3/files?' . http_build_query($queryParams));

            if (!$response->successful()) {
                Log::error('Error al subir archivo a Google Drive', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'fileName' => $fileName,
                ]);
                throw new \Exception('Error al subir archivo a Google Drive: ' . $response->body());
            }

            $file = $response->json();
            
            $result = [
                'id' => $file['id'],
                'name' => $file['name'],
                'webViewLink' => 'https://drive.google.com/file/d/' . $file['id'] . '/view',
                'webContentLink' => $file['webContentLink'] ?? null,
            ];
            
            // Registrar operación exitosa
            $this->logOperation('upload', 'file', $fileName, $file['id'], $result['webViewLink'], 'success', null, [
                'parent_folder_id' => $parentFolderId,
                'mime_type' => $mimeType,
            ], null, $registrationId, $companyId);
            
            return $result;
        } catch (\Exception $e) {
            // Registrar operación fallida
            $this->logOperation('upload', 'file', $fileName, null, null, 'failed', $e->getMessage(), [
                'parent_folder_id' => $parentFolderId,
                'mime_type' => $mimeType,
            ], null, $registrationId, $companyId);
            
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
     * Retorna array con 'success' y 'message'
     */
    public function testConnection()
    {
        try {
            // Verificar que el JSON esté configurado
            $serviceAccountJson = $this->settings->drive_service_account_json;
            
            if (empty($serviceAccountJson)) {
                return [
                    'success' => false,
                    'message' => 'Google Drive no está configurado. Por favor, configura el JSON de Service Account.',
                ];
            }

            // Validar JSON
            $serviceAccount = json_decode($serviceAccountJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'El JSON de Service Account no es válido: ' . json_last_error_msg(),
                ];
            }

            // Obtener token
            $token = $this->getAccessToken();
            
            // Intentar listar archivos para verificar conexión
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/files', [
                    'pageSize' => 1,
                    'q' => "mimeType='application/vnd.google-apps.folder'",
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con Google Drive API. La Service Account está configurada correctamente.',
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                
                // Mensaje específico para API no habilitada
                if (str_contains($errorMessage, 'API has not been used') || 
                    str_contains($errorMessage, 'API not enabled') ||
                    str_contains($errorMessage, 'API activation')) {
                    return [
                        'success' => false,
                        'message' => 'La API de Google Drive no está habilitada. Por favor, habilítala en Google Cloud Console: https://console.cloud.google.com/apis/library/drive.googleapis.com',
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Error al conectar con Google Drive: ' . $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            // Mensaje específico para errores comunes
            if (str_contains($message, 'Google Drive no está configurado')) {
                return [
                    'success' => false,
                    'message' => $message,
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al probar conexión: ' . $message,
            ];
        }
    }

    /**
     * Obtener o crear carpeta base para expedientes sin cliente
     */
    public function getOrCreateNoClientFolder()
    {
        $folderName = $this->settings->drive_folder_name_no_client ?: 'Expedientes Sin Cliente';
        $baseFolderId = $this->settings->drive_folder_id;
        
        // Buscar si ya existe una carpeta con ese nombre en la carpeta base
        try {
            $token = $this->getAccessToken();
            
            $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
            if ($baseFolderId) {
                $query .= " and '{$baseFolderId}' in parents";
            }
            
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/files', [
                    'q' => $query,
                    'fields' => 'files(id, name)',
                ]);
            
            if ($response->successful()) {
                $files = $response->json()['files'] ?? [];
                if (!empty($files)) {
                    return $files[0]['id'];
                }
            }
            
            // Si no existe, crearla
            $folder = $this->createFolder($folderName, $baseFolderId);
            return $folder['id'];
        } catch (\Exception $e) {
            Log::error('Error al obtener/crear carpeta de expedientes sin cliente', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener o crear carpeta base para clientes
     */
    public function getOrCreateClientsFolder()
    {
        $folderName = $this->settings->drive_folder_name_with_client ?: 'Clientes';
        $baseFolderId = $this->settings->drive_folder_id;
        
        // Buscar si ya existe una carpeta con ese nombre en la carpeta base
        try {
            $token = $this->getAccessToken();
            
            $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
            if ($baseFolderId) {
                $query .= " and '{$baseFolderId}' in parents";
            }
            
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/files', [
                    'q' => $query,
                    'fields' => 'files(id, name)',
                ]);
            
            if ($response->successful()) {
                $files = $response->json()['files'] ?? [];
                if (!empty($files)) {
                    return $files[0]['id'];
                }
            }
            
            // Si no existe, crearla
            $folder = $this->createFolder($folderName, $baseFolderId);
            return $folder['id'];
        } catch (\Exception $e) {
            Log::error('Error al obtener/crear carpeta de clientes', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener MIME type por extensión de archivo
     */
    protected function getMimeTypeByExtension($extension)
    {
        $mimeTypes = [
            // Documentos
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            
            // Imágenes
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            
            // Texto
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            
            // Archivos comprimidos
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            
            // Otros
            'rtf' => 'application/rtf',
            'xps' => 'application/vnd.ms-xpsdocument',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    /**
     * Registrar operación en el log
     */
    protected function logOperation(
        string $operationType,
        string $resourceType,
        string $resourceName,
        ?string $driveId = null,
        ?string $driveUrl = null,
        string $status = 'pending',
        ?string $errorMessage = null,
        array $details = [],
        ?int $userId = null,
        ?int $registrationId = null,
        ?int $companyId = null
    ): void {
        try {
            // Solo registrar si hay un usuario autenticado
            if (!auth()->check()) {
                return;
            }
            
            \App\Models\DriveOperationLog::create([
                'operation_type' => $operationType,
                'resource_type' => $resourceType,
                'resource_name' => $resourceName,
                'drive_id' => $driveId,
                'drive_url' => $driveUrl,
                'status' => $status,
                'error_message' => $errorMessage,
                'details' => $details,
                'user_id' => $userId ?? auth()->id(),
                'registration_id' => $registrationId,
                'company_id' => $companyId,
            ]);
        } catch (\Exception $e) {
            // No fallar si no se puede registrar el log
            Log::warning('Error al registrar operación de Drive en log', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
