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
     * Aplica la configuración de correo desde settings y reinicia el transporte SMTP.
     */
    public function applyMailConfiguration(?string $fromEmail = null, ?string $fromName = null): void
    {
        $provider = $this->settings->mail_provider ?? 'smtp';
        $fromEmail = $fromEmail ?? ($provider === 'zoho'
            ? $this->settings->zoho_from_email
            : $this->settings->mail_from_address);
        $fromName = $fromName ?? $this->settings->mail_from_name;

        if ($provider === 'zoho') {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.scheme' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.zoho.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.username' => $this->settings->zoho_from_email,
                'mail.mailers.smtp.password' => $this->settings->mail_password ?: $this->settings->zoho_access_token,
                'mail.from.address' => $fromEmail,
                'mail.from.name' => $fromName,
            ]);
        } else {
            $port = (int) ($this->settings->mail_port ?: 587);
            $encryption = strtolower((string) ($this->settings->mail_encryption ?? 'tls'));
            $scheme = ($encryption === 'ssl' || $port === 465) ? 'smtps' : 'smtp';

            config([
                'mail.default' => $this->settings->mail_mailer ?: 'smtp',
                'mail.mailers.smtp.scheme' => $scheme,
                'mail.mailers.smtp.host' => $this->settings->mail_host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $this->settings->mail_username,
                'mail.mailers.smtp.password' => $this->settings->mail_password,
                'mail.from.address' => $fromEmail,
                'mail.from.name' => $fromName,
            ]);
        }

        Mail::purge('smtp');
    }

    /**
     * Valida que la configuración de correo esté completa.
     *
     * @throws \RuntimeException
     */
    public function assertMailConfiguration(): void
    {
        if (($this->settings->mail_provider ?? 'smtp') === 'zoho') {
            if (empty($this->settings->zoho_from_email)) {
                throw new \RuntimeException('Configure el email remitente de Zoho en Sistema → Configuración → Correo.');
            }
            if (empty($this->settings->zoho_refresh_token)) {
                throw new \RuntimeException('Autorice la aplicación con Zoho en Sistema → Configuración → Correo.');
            }

            return;
        }

        $missing = [];
        if (empty($this->settings->mail_host)) {
            $missing[] = 'servidor SMTP';
        }
        if (empty($this->settings->mail_username)) {
            $missing[] = 'usuario SMTP';
        }
        if (empty($this->settings->mail_password)) {
            $missing[] = 'contraseña SMTP';
        }
        if (empty($this->settings->mail_from_address)) {
            $missing[] = 'email remitente';
        }

        if ($missing !== []) {
            throw new \RuntimeException(
                'Configuración de correo incompleta ('.implode(', ', $missing).'). Revise Sistema → Configuración → Correo.'
            );
        }
    }

    /**
     * Envía un Mailable usando el proveedor configurado (SMTP o Zoho API).
     */
    public function sendMailable(string $to, \Illuminate\Mail\Mailable $mailable): void
    {
        $this->assertMailConfiguration();

        if (($this->settings->mail_provider ?? 'smtp') === 'zoho') {
            $this->sendMailableViaZoho($to, $mailable);

            return;
        }

        $this->applyMailConfiguration();
        Mail::to($to)->send($mailable);
    }

    /**
     * @throws \RuntimeException
     */
    protected function sendMailableViaZoho(string $to, \Illuminate\Mail\Mailable $mailable): void
    {
        $subject = 'Notificación';
        if (method_exists($mailable, 'envelope')) {
            $subject = $mailable->envelope()->subject ?? $subject;
        }

        $html = $mailable->render();
        [$pdfBinary, $pdfName] = $this->extractFirstAttachmentBinary($mailable);

        $accessToken = $this->getZohoAccessToken();
        if (! $accessToken) {
            throw new \RuntimeException('No se pudo obtener el token de Zoho. Verifique la autorización en Configuración → Correo.');
        }

        $fromEmail = $this->settings->zoho_from_email;
        $accountId = $this->getZohoAccountId($accessToken, $fromEmail);
        if (! $accountId) {
            throw new \RuntimeException('No se encontró la cuenta de Zoho para '.$fromEmail.'.');
        }

        $zohoAttachments = [];
        if ($pdfBinary !== null && $pdfBinary !== '') {
            $uploaded = $this->uploadZohoAttachment($accessToken, $accountId, $pdfName, $pdfBinary);
            if ($uploaded) {
                $zohoAttachments[] = $uploaded;
            }
        }

        $emailData = [
            'fromAddress' => $fromEmail,
            'toAddress' => $to,
            'subject' => $subject,
            'content' => $html,
            'mailFormat' => 'html',
        ];

        if ($zohoAttachments !== []) {
            $emailData['attachments'] = $zohoAttachments;
        }

        $apiUrl = 'https://mail.zoho.com/api/accounts/'.urlencode($accountId).'/messages';
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken '.$accessToken,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, $emailData);

        if (! $response->successful()) {
            throw new \RuntimeException($this->formatZohoError($response, $fromEmail));
        }
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    protected function extractFirstAttachmentBinary(\Illuminate\Mail\Mailable $mailable): array
    {
        $name = 'adjunto.pdf';
        $binary = null;

        if (! method_exists($mailable, 'attachments')) {
            return [$binary, $name];
        }

        foreach ($mailable->attachments() as $attachment) {
            $attachment->attachWith(
                fn () => null,
                function ($data) use (&$binary, &$name, $attachment) {
                    $name = $attachment->as ?? $name;
                    $binary = $data();
                }
            );

            if ($binary !== null) {
                break;
            }
        }

        return [$binary, $name];
    }

    /**
     * @return array{storeName: string, attachmentPath: string, attachmentName: string}|null
     */
    protected function uploadZohoAttachment(string $accessToken, string $accountId, string $fileName, string $binary): ?array
    {
        $url = 'https://mail.zoho.com/api/accounts/'.urlencode($accountId).'/messages/attachments'
            .'?fileName='.urlencode($fileName).'&isInline=false';

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken '.$accessToken,
            'Content-Type' => 'application/octet-stream',
        ])->withBody($binary, 'application/octet-stream')->post($url);

        if (! $response->successful()) {
            Log::warning('No se pudo subir adjunto a Zoho', ['body' => $response->body()]);

            return null;
        }

        $data = $response->json('data.0') ?? $response->json('data') ?? $response->json();

        if (! is_array($data)) {
            return null;
        }

        $storeName = $data['storeName'] ?? null;
        $attachmentPath = $data['attachmentPath'] ?? $data['attachmentpath'] ?? null;
        $attachmentName = $data['attachmentName'] ?? $data['attachmentname'] ?? $fileName;

        if (! $storeName || ! $attachmentPath) {
            return null;
        }

        return [
            'storeName' => $storeName,
            'attachmentPath' => $attachmentPath,
            'attachmentName' => $attachmentName,
        ];
    }

    protected function formatZohoError(\Illuminate\Http\Client\Response $response, string $fromEmail): string
    {
        $errorData = $response->json();
        if (isset($errorData['data']['errorCode']) && $errorData['data']['errorCode'] === 'URL_RULE_NOT_CONFIGURED') {
            return 'El token de Zoho no corresponde al email remitente ('.$fromEmail.'). Vuelva a autorizar Zoho con esa misma cuenta.';
        }

        $message = $errorData['message'] ?? $errorData['error'] ?? $response->body();

        return 'Error al enviar vía Zoho: '.$message;
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
            $this->applyMailConfiguration($fromEmail, $fromName);

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

            // Obtener el accountId de Zoho (requerido para el endpoint)
            $accountId = $this->getZohoAccountId($accessToken, $fromEmail);
            
            if (!$accountId) {
                throw new \Exception('No se pudo obtener el Account ID de Zoho para el email: ' . $fromEmail . '. Verifica que el token esté vinculado a la cuenta correcta.');
            }

            // Preparar el correo
            $emailData = [
                'fromAddress' => $fromEmail,
                'toAddress' => is_array($to) ? implode(',', $to) : $to,
                'subject' => $subject,
                'content' => $body,
                'mailFormat' => 'html',
            ];

            // Enviar correo vía Zoho Mail API usando accountId
            $apiUrl = 'https://mail.zoho.com/api/accounts/' . urlencode($accountId) . '/messages';
            
            Log::info('Enviando correo Zoho', [
                'fromEmail' => $fromEmail,
                'accountId' => $accountId,
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
                    $errorMessage .= "\n\n1️⃣  Ve a Configuración → Correo & SMTP en Invoices";
                    $errorMessage .= "\n2️⃣  Verifica que Email Remitente sea: " . $fromEmail;
                    $errorMessage .= "\n3️⃣  Haz clic en \"Limpiar\" en el campo Refresh Token";
                    $errorMessage .= "\n4️⃣  Guarda los cambios";
                    $errorMessage .= "\n5️⃣  🔴 CRÍTICO: Cierra sesión en Zoho o abre ventana privada/incógnito";
                    $errorMessage .= "\n6️⃣  Inicia sesión en Zoho SOLO con: " . $fromEmail;
                    $errorMessage .= "\n7️⃣  Vuelve a Invoices y haz clic en \"Autorizar con Zoho\"";
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
     * Obtener accountId de Zoho para un email específico
     */
    protected function getZohoAccountId($accessToken, $email)
    {
        try {
            // Obtener lista de cuentas del usuario autenticado
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->get('https://mail.zoho.com/api/accounts');
            
            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Respuesta de Zoho accounts API', [
                    'data_structure' => $data,
                ]);
                
                // Buscar la cuenta que coincida con el email
                // La estructura puede variar, intentar diferentes formatos
                $accounts = null;
                
                if (isset($data['data']) && is_array($data['data'])) {
                    $accounts = $data['data'];
                } elseif (isset($data['accounts']) && is_array($data['accounts'])) {
                    $accounts = $data['accounts'];
                } elseif (is_array($data) && isset($data[0])) {
                    $accounts = $data;
                }
                
                if ($accounts && is_array($accounts)) {
                    foreach ($accounts as $account) {
                        if (!is_array($account)) {
                            continue;
                        }
                        
                        // Obtener emailAddress - puede ser string o estar en diferentes campos
                        $accountEmail = null;
                        if (isset($account['emailAddress']) && is_string($account['emailAddress'])) {
                            $accountEmail = $account['emailAddress'];
                        } elseif (isset($account['email']) && is_string($account['email'])) {
                            $accountEmail = $account['email'];
                        } elseif (isset($account['emailAddress']) && is_array($account['emailAddress'])) {
                            // Si es array, tomar el primer elemento o buscar 'address'
                            if (isset($account['emailAddress']['address'])) {
                                $accountEmail = $account['emailAddress']['address'];
                            } elseif (isset($account['emailAddress'][0])) {
                                $accountEmail = is_string($account['emailAddress'][0]) 
                                    ? $account['emailAddress'][0] 
                                    : ($account['emailAddress'][0]['address'] ?? null);
                            }
                        }
                        
                        // Obtener accountId
                        $accountId = $account['accountId'] ?? $account['id'] ?? null;
                        
                        if ($accountId && $accountEmail && is_string($accountEmail)) {
                            // Comparar emails (case-insensitive)
                            if (strtolower($accountEmail) === strtolower($email)) {
                                Log::info('AccountId encontrado para email', [
                                    'email' => $email,
                                    'accountId' => $accountId,
                                    'accountEmail' => $accountEmail,
                                ]);
                                return $accountId;
                            }
                        }
                    }
                }
                
                // Si no encontramos por email, intentar usar el primer accountId disponible
                if ($accounts && isset($accounts[0])) {
                    $firstAccount = $accounts[0];
                    $firstAccountId = $firstAccount['accountId'] ?? $firstAccount['id'] ?? null;
                    
                    if ($firstAccountId) {
                        Log::warning('No se encontró accountId para email específico, usando primer accountId disponible', [
                            'email' => $email,
                            'accountId' => $firstAccountId,
                            'firstAccount' => $firstAccount,
                        ]);
                        return $firstAccountId;
                    }
                }
            } else {
                Log::error('Error obteniendo accounts de Zoho', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Excepción obteniendo accountId de Zoho: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        return null;
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
