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
    public string $drive_folder_id = '';
    public string $drive_folder_name_no_client = 'Expedientes Sin Cliente';
    public string $drive_folder_name_with_client = 'Clientes';
    public string $drive_mode = 'service_account'; // 'service_account' | 'oauth_user'
    public string $drive_oauth_client_id = '';
    public string $drive_oauth_client_secret = '';
    public string $drive_oauth_refresh_token = '';
    public string $drive_oauth_access_token = '';

    // Mail Provider
    public string $mail_provider = 'smtp'; // 'smtp' o 'zoho'
    
    // SMTP
    public string $mail_mailer = 'smtp';
    public string $mail_host = 'smtp.gmail.com';
    public int $mail_port = 587;
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = 'noreply@rams.com';
    public string $mail_from_name = 'RAMS Sistema';
    
    // Zoho Mail API
    public string $zoho_client_id = '';
    public string $zoho_client_secret = '';
    public string $zoho_refresh_token = '';
    public string $zoho_access_token = '';
    public string $zoho_from_email = '';
    
    // Sistema y Personalización
    public string $footer_text = 'RAMS - Regulatory Affairs Management System';
    public string $system_name = 'Sistema de Gestión Regulatoria';

    // Plantilla PDF de cotizaciones (cabecera y pie editables)
    public string $quote_pdf_header_subtitle = 'RAMS - Regulatory Affairs Management System';
    public string $quote_pdf_footer_text = '';

    public static function group(): string
    {
        return 'general';
    }
}
