<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $rows = [
            'legal_privacy_title' => 'Política de Privacidad',
            'legal_terms_title' => 'Términos y Condiciones del Servicio',
            'legal_show_privacy_on_login' => true,
            'legal_show_terms_on_login' => true,
        ];

        foreach ($rows as $name => $value) {
            $exists = DB::table('settings')
                ->where('group', 'general')
                ->where('name', $name)
                ->exists();

            if (! $exists) {
                $this->migrator->add('general.'.$name, $value);
            }
        }
    }
};
