<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Datos de Agencia (Marca Blanca)
    public string $agency_name = 'RAMS';
    public string $agency_nit = '';
    public string $agency_address = '';
    public string $agency_phone = '';
    public string $agency_email = '';
    public string $agency_website = '';
    public string $agency_logo = '';

    // Google Drive
    public string $drive_service_account_json = '';

    // SMTP
    public string $mail_mailer = 'smtp';
    public string $mail_host = 'smtp.gmail.com';
    public int $mail_port = 587;
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = 'noreply@rams.com';
    public string $mail_from_name = 'RAMS Sistema';

    public static function group(): string
    {
        return 'general';
    }
}
