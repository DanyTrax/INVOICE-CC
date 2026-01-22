<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.agency_name', 'RAMS');
        $this->migrator->add('general.agency_nit', '');
        $this->migrator->add('general.agency_address', '');
        $this->migrator->add('general.agency_phone', '');
        $this->migrator->add('general.agency_email', '');
        $this->migrator->add('general.agency_website', '');
        $this->migrator->add('general.agency_logo', '');
        $this->migrator->add('general.drive_service_account_json', '');
        $this->migrator->add('general.mail_mailer', 'smtp');
        $this->migrator->add('general.mail_host', 'smtp.gmail.com');
        $this->migrator->add('general.mail_port', 587);
        $this->migrator->add('general.mail_username', '');
        $this->migrator->add('general.mail_password', '');
        $this->migrator->add('general.mail_encryption', 'tls');
        $this->migrator->add('general.mail_from_address', 'noreply@rams.com');
        $this->migrator->add('general.mail_from_name', 'RAMS Sistema');
    }
};
