<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Datos de Agencia (Marca Blanca)
    public string $agency_name;
    public string $agency_nit;
    public ?string $agency_logo_path;

    // Google Drive
    public ?string $drive_service_account_json;

    // SMTP
    public ?string $smtp_host;
    public ?int $smtp_port;
    public ?string $smtp_encryption;
    public ?string $smtp_username;
    public ?string $smtp_password;
    public ?string $smtp_from_address;
    public ?string $smtp_from_name;

    public static function group(): string
    {
        return 'general';
    }
}
