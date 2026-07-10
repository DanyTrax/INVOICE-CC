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

    public string $drive_folder_name_no_client = 'Solicitudes sin cliente';

    public string $drive_folder_name_with_client = 'Clientes';

    /** País por defecto para la carpeta "Solicitudes sin cliente" (estructura: Base → País → Solicitudes sin cliente). Vacío = bajo carpeta base. */
    public string $drive_default_country_no_client = '';

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

    public string $mail_from_name = 'Invoices';

    // Zoho Mail API
    public string $zoho_client_id = '';

    public string $zoho_client_secret = '';

    public string $zoho_refresh_token = '';

    public string $zoho_access_token = '';

    public string $zoho_from_email = '';

    // Sistema y Personalización
    public string $footer_text = 'Invoices - Dashboard de Recaudos';

    public string $system_name = 'Invoices';

    public string $timezone = 'America/Bogota';

    /** En panel admin (escritorio): si es true, el menú lateral inicia desplegado; si false, solo iconos. */
    public bool $admin_sidebar_expanded_default = false;

    /** Bloqueo por IP tras intentos fallidos en /login (credenciales incorrectas, cuenta inactiva, etc.). */
    public bool $login_lockout_enabled = true;

    public int $login_max_failed_attempts = 5;

    public int $login_lockout_duration_minutes = 30;

    /** Títulos de las páginas públicas (y texto del enlace en login si está visible). */
    public string $legal_privacy_title = 'Política de Privacidad';

    public string $legal_terms_title = 'Términos y Condiciones del Servicio';

    /** Mostrar enlaces en la pantalla de login. */
    public bool $legal_show_privacy_on_login = true;

    public bool $legal_show_terms_on_login = true;

    /** Si es false, el sistema no exige ni ofrece 2FA en login ni perfil. */
    public bool $two_factor_system_enabled = true;

    /** HTML público (política y términos). Vacío = usar texto por defecto del sistema. */
    public string $legal_privacy_html = '';

    public string $legal_terms_html = '';

    // Plantilla PDF de cotizaciones (cabecera y pie editables)
    public string $quote_pdf_header_subtitle = 'RAMS - Regulatory Affairs Management System';

    public string $quote_pdf_footer_text = '';

    public static function group(): string
    {
        return 'general';
    }
}
