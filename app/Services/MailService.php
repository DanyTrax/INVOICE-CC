<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            // Obtener o refrescar access token
            $accessToken = $this->getZohoAccessToken();
            
            if (!$accessToken) {
                Log::error('No se pudo obtener el access token de Zoho');
                return false;
            }

            $fromEmail = $fromEmail ?? $this->settings->zoho_from_email;
            $fromName = $fromName ?? $this->settings->mail_from_name;

            // Preparar el correo
            $emailData = [
                'fromAddress' => $fromEmail,
                'toAddress' => is_array($to) ? implode(',', $to) : $to,
                'subject' => $subject,
                'content' => $body,
                'mailFormat' => 'html',
            ];

            // Enviar correo vía Zoho Mail API
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://mail.zoho.com/api/accounts/' . urlencode($fromEmail) . '/messages', $emailData);

            if ($response->successful()) {
                return true;
            } else {
                $errorBody = $response->body();
                Log::error('Error enviando correo Zoho: ' . $errorBody);
                throw new \Exception('Error Zoho: ' . $errorBody);
            }
        } catch (\Exception $e) {
            Log::error('Error enviando correo Zoho: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el método send() pueda capturarlo
        }
    }

    /**
     * Obtener access token de Zoho (usando refresh token)
     */
    protected function getZohoAccessToken()
    {
        // Si ya tenemos un access token válido, usarlo
        if (!empty($this->settings->zoho_access_token)) {
            // TODO: Verificar si el token aún es válido (no expiró)
            // Por ahora, siempre refrescamos para asegurar validez
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
                }
            } else {
                Log::error('Error obteniendo access token de Zoho: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Excepción obteniendo access token de Zoho: ' . $e->getMessage());
        }

        return null;
    }
}
