<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Verificar y agregar solo si no existe
        $this->addIfNotExists('general.agency_name', 'RAMS');
        $this->addIfNotExists('general.agency_nit', '');
        $this->addIfNotExists('general.agency_address', '');
        $this->addIfNotExists('general.agency_phone', '');
        $this->addIfNotExists('general.agency_email', '');
        $this->addIfNotExists('general.agency_website', '');
        $this->addIfNotExists('general.agency_logo', '');
        $this->addIfNotExists('general.drive_service_account_json', '');
        $this->addIfNotExists('general.drive_folder_id', '');
        $this->addIfNotExists('general.mail_provider', 'smtp');
        $this->addIfNotExists('general.mail_mailer', 'smtp');
        $this->addIfNotExists('general.mail_host', 'smtp.gmail.com');
        $this->addIfNotExists('general.mail_port', 587);
        $this->addIfNotExists('general.mail_username', '');
        $this->addIfNotExists('general.mail_password', '');
        $this->addIfNotExists('general.mail_encryption', 'tls');
        $this->addIfNotExists('general.mail_from_address', 'noreply@rams.com');
        $this->addIfNotExists('general.mail_from_name', 'RAMS Sistema');
        $this->addIfNotExists('general.zoho_client_id', '');
        $this->addIfNotExists('general.zoho_client_secret', '');
        $this->addIfNotExists('general.zoho_refresh_token', '');
        $this->addIfNotExists('general.zoho_access_token', '');
        $this->addIfNotExists('general.zoho_from_email', '');
        $this->addIfNotExists('general.footer_text', 'RAMS - Regulatory Affairs Management System');
        $this->addIfNotExists('general.system_name', 'Sistema de Gestión Regulatoria');
        $this->addIfNotExists('general.drive_folder_name_no_client', 'Expedientes Sin Cliente');
        $this->addIfNotExists('general.drive_folder_name_with_client', 'Clientes');
        $this->addIfNotExists('general.drive_mode', 'service_account');
        $this->addIfNotExists('general.drive_oauth_client_id', '');
        $this->addIfNotExists('general.drive_oauth_client_secret', '');
        $this->addIfNotExists('general.drive_oauth_refresh_token', '');
        $this->addIfNotExists('general.drive_oauth_access_token', '');
    }

    /**
     * Agregar setting solo si no existe
     */
    protected function addIfNotExists(string $key, $defaultValue): void
    {
        $exists = DB::table('settings')
            ->where('group', 'general')
            ->where('name', str_replace('general.', '', $key))
            ->exists();

        if (!$exists) {
            $this->migrator->add($key, $defaultValue);
        }
    }
};
