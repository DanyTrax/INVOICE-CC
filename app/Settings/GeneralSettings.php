<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Datos de Agencia (Marca Blanca)
    public string $agency_name;
    public ?string $agency_nit;
    public ?string $agency_address;
    public ?string $agency_phone;
    public ?string $agency_email;
    public ?string $agency_website;
    public ?string $agency_logo;

    // Google Drive
    public ?string $drive_service_account_json;

    // SMTP
    public string $mail_mailer;
    public string $mail_host;
    public int $mail_port;
    public ?string $mail_username;
    public ?string $mail_password;
    public ?string $mail_encryption;
    public string $mail_from_address;
    public string $mail_from_name;

    public static function group(): string
    {
        return 'general';
    }
}
