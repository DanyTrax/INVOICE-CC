<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class MailService
{
    protected GeneralSettings $settings;

    public function __construct()
    {
        $this->settings = app(GeneralSettings::class);
    }

    /**
     * Enviar correo usando el proveedor configurado
     */
    public function send($to, $subject, $body, $fromName = null, $fromEmail = null, $isTest = false)
    {
        $provider = $this->settings->mail_provider;
        $fromEmail = $fromEmail ?? ($provider === 'zoho' ? $this->settings->zoho_from_email : $this->settings->mail_from_address);
        $fromName = $fromName ?? $this->settings->mail_from_name;
        
        // Crear log antes de enviar
        $emailLog = EmailLog::create([
            'to' => is_array($to) ? implode(', ', $to) : $to,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'subject' => $subject,
            'body' => $body,
            'provider' => $provider,
            'status' => 'pending',
            'user_id' => Auth::id(),
            'is_test' => $isTest,
        ]);
        
        try {
            $result = false;
            if ($provider === 'zoho') {
                $result = $this->sendViaZoho($to, $subject, $body, $fromName, $fromEmail);
            } else {
                $result = $this->sendViaSmtp($to, $subject, $body, $fromName, $fromEmail);
            }
            
            // Actualizar log con resultado
            $emailLog->update([
                'status' => $result ? 'sent' : 'failed',
                'error_message' => $result ? null : 'Error al enviar correo',
            ]);
            
            return $result;
        } catch (\Exception $e) {
            // Actualizar log con error
            $emailLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            Log::error('Error enviando correo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar correo vía SMTP
     */
    protected function sendViaSmtp($to, $subject, $body, $fromName = null, $fromEmail = null)
    {
        try {
            // Configurar mail dinámicamente desde settings
            config([
                'mail.default' => $this->settings->mail_mailer,
                'mail.mailers.smtp.host' => $this->settings->mail_host,
                'mail.mailers.smtp.port' => $this->settings->mail_port,
                'mail.mailers.smtp.username' => $this->settings->mail_username,
                'mail.mailers.smtp.password' => $this->settings->mail_password,
                'mail.mailers.smtp.encryption' => $this->settings->mail_encryption,
                'mail.from.address' => $fromEmail ?? $this->settings->mail_from_address,
                'mail.from.name' => $fromName ?? $this->settings->mail_from_name,
            ]);

            Mail::html($body, function ($message) use ($to, $subject, $fromName, $fromEmail) {
                $message->to($to)
                        ->subject($subject)
                        ->from(
                            $fromEmail ?? $this->settings->mail_from_address,
                            $fromName ?? $this->settings->mail_from_name
                        );
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Error enviando correo SMTP: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el método send() pueda capturarlo
        }
    }

    /**
     * Enviar correo vía Zoho Mail API
     */
    protected function sendViaZoho($to, $subject, $body, $fromName = null, $fromEmail = null)
    {
        try {
            // Validar configuración de Zoho
            if (empty($this->settings->zoho_from_email)) {
                throw new \Exception('El email de origen de Zoho no está configurado. Por favor, configura "Zoho From Email" en la configuración de correo.');
            }
            
            if (empty($this->settings->zoho_refresh_token)) {
                throw new \Exception('El Refresh Token de Zoho no está configurado. Por favor, autoriza la aplicación con Zoho primero.');
            }
            
            // Obtener o refrescar access token
            $accessToken = $this->getZohoAccessToken();
            
            if (!$accessToken) {
                $errorMsg = 'No se pudo obtener el access token de Zoho. Verifica que el Refresh Token, Client ID y Client Secret estén correctamente configurados.';
                Log::error($errorMsg);
                throw new \Exception($errorMsg);
            }

            $fromEmail = $fromEmail ?? $this->settings->zoho_from_email;
            $fromName = $fromName ?? $this->settings->mail_from_name;
            
            if (empty($fromEmail)) {
                throw new \Exception('El email de origen no está configurado.');
            }

            // Preparar el correo
            $emailData = [
                'fromAddress' => $fromEmail,
                'toAddress' => is_array($to) ? implode(',', $to) : $to,
                'subject' => $subject,
                'content' => $body,
                'mailFormat' => 'html',
            ];

            // Primero, intentar obtener el accountId del email (opcional, pero mejora la precisión)
            // Si falla, usaremos el email directamente en la URL
            $accountId = $fromEmail; // Por defecto usamos el email
            
            // Enviar correo vía Zoho Mail API
            // Nota: Zoho acepta tanto accountId numérico como email en la URL
            // El error URL_RULE_NOT_CONFIGURED generalmente indica que el token
            // está vinculado a una cuenta diferente al fromEmail
            $apiUrl = 'https://mail.zoho.com/api/accounts/' . urlencode($accountId) . '/messages';
            
            Log::info('Enviando correo Zoho', [
                'fromEmail' => $fromEmail,
                'to' => $to,
                'apiUrl' => $apiUrl,
                'hasAccessToken' => !empty($accessToken),
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, $emailData);

            if ($response->successful()) {
                return true;
            } else {
                // Intentar obtener mensaje de error más detallado
                $errorData = $response->json();
                $errorMessage = 'Error al enviar correo vía Zoho Mail API';
                
                // Manejar error específico de URL_RULE_NOT_CONFIGURED
                if (isset($errorData['data']['errorCode']) && $errorData['data']['errorCode'] === 'URL_RULE_NOT_CONFIGURED') {
                    $errorMessage = '❌ ERROR: El Refresh Token está vinculado a una cuenta DIFERENTE al Email Remitente.';
                    $errorMessage .= "\n\n";
                    $errorMessage .= '🔍 DIAGNÓSTICO:';
                    $errorMessage .= "\n• Email Remitente configurado: " . $fromEmail;
                    $errorMessage .= "\n• El token fue generado autorizando con OTRA cuenta de Zoho";
                    $errorMessage .= "\n• Zoho solo permite enviar desde la cuenta que autorizó la aplicación";
                    $errorMessage .= "\n\n";
                    $errorMessage .= '✅ SOLUCIÓN (Pasos EXACTOS):';
                    $errorMessage .= "\n\n1️⃣  Ve a Configuración → Correo & SMTP en RAMS";
                    $errorMessage .= "\n2️⃣  Verifica que Email Remitente sea: " . $fromEmail;
                    $errorMessage .= "\n3️⃣  Haz clic en \"Limpiar\" en el campo Refresh Token";
                    $errorMessage .= "\n4️⃣  Guarda los cambios";
                    $errorMessage .= "\n5️⃣  🔴 CRÍTICO: Cierra sesión en Zoho o abre ventana privada/incógnito";
                    $errorMessage .= "\n6️⃣  Inicia sesión en Zoho SOLO con: " . $fromEmail;
                    $errorMessage .= "\n7️⃣  Vuelve a RAMS y haz clic en \"Autorizar con Zoho\"";
                    $errorMessage .= "\n8️⃣  En Zoho, VERIFICA que la cuenta que autoriza sea: " . $fromEmail;
                    $errorMessage .= "\n9️⃣  Acepta los permisos";
                    $errorMessage .= "\n🔟 Intenta enviar el correo de nuevo";
                    $errorMessage .= "\n\n";
                    $errorMessage .= '⚠️  IMPORTANTE: El token DEBE generarse con la MISMA cuenta que el Email Remitente.';
                    $errorMessage .= "\n   Si autorizas con otra cuenta (ej. tu correo personal), los envíos fallarán.";
                } elseif (isset($errorData['error'])) {
                    $errorMessage .= ': ' . $errorData['error'];
                    if (isset($errorData['message'])) {
                        $errorMessage .= ' - ' . $errorData['message'];
                    }
                } elseif (isset($errorData['message'])) {
                    $errorMessage .= ': ' . $errorData['message'];
                } else {
                    $errorBody = $response->body();
                    $errorMessage .= '. Respuesta: ' . (strlen($errorBody) > 200 ? substr($errorBody, 0, 200) . '...' : $errorBody);
                }
                
                // Agregar código de estado HTTP
                $errorMessage .= ' (HTTP ' . $response->status() . ')';
                
                Log::error('Error enviando correo Zoho: ' . $errorMessage);
                Log::error('URL: ' . $apiUrl);
                Log::error('Response: ' . $response->body());
                
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Excepción enviando correo Zoho: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el método send() pueda capturarlo
        }
    }

    /**
     * Obtener access token de Zoho (usando refresh token)
     */
    protected function getZohoAccessToken()
    {
        // Validar que tengamos los datos necesarios
        if (empty($this->settings->zoho_refresh_token)) {
            Log::error('Refresh Token de Zoho no configurado');
            return null;
        }
        
        if (empty($this->settings->zoho_client_id)) {
            Log::error('Client ID de Zoho no configurado');
            return null;
        }
        
        if (empty($this->settings->zoho_client_secret)) {
            Log::error('Client Secret de Zoho no configurado');
            return null;
        }

        try {
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'refresh_token' => $this->settings->zoho_refresh_token,
                'client_id' => $this->settings->zoho_client_id,
                'client_secret' => $this->settings->zoho_client_secret,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'] ?? null;
                
                if ($accessToken) {
                    // Guardar el nuevo access token en settings
                    $this->settings->zoho_access_token = $accessToken;
                    $this->settings->save();
                    
                    return $accessToken;
                } else {
                    Log::error('No se recibió access_token en la respuesta de Zoho: ' . json_encode($data));
                }
            } else {
                $errorData = $response->json();
                $errorMsg = 'Error obteniendo access token de Zoho';
                
                if (isset($errorData['error'])) {
                    $errorMsg .= ': ' . $errorData['error'];
                    if (isset($errorData['error_description'])) {
                        $errorMsg .= ' - ' . $errorData['error_description'];
                    }
                } else {
                    $errorMsg .= ': ' . $response->body();
                }
                
                Log::error($errorMsg . ' (HTTP ' . $response->status() . ')');
            }
        } catch (\Exception $e) {
            Log::error('Excepción obteniendo access token de Zoho: ' . $e->getMessage());
        }

        return null;
    }
}
