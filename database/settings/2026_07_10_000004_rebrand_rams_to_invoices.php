<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('general.mail_from_name', function ($value) {
            return in_array($value, ['RAMS Sistema', 'RAMS'], true) ? 'Invoices' : $value;
        });

        $this->migrator->update('general.footer_text', function ($value) {
            return $value === 'RAMS - Regulatory Affairs Management System'
                ? 'Invoices - Dashboard de Recaudos'
                : $value;
        });

        $this->migrator->update('general.system_name', function ($value) {
            return in_array($value, ['Sistema de Gestión Regulatoria', 'RAMS'], true) ? 'Invoices' : $value;
        });

        $this->migrator->update('general.agency_name', function ($value) {
            return $value === 'RAMS' ? 'Invoices' : $value;
        });
    }
};
