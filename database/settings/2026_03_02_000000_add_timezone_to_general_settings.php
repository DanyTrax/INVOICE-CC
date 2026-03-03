<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Asegurar que exista la clave general.timezone para entornos ya instalados
        $exists = DB::table('settings')
            ->where('group', 'general')
            ->where('name', 'timezone')
            ->exists();

        if (!$exists) {
            $this->migrator->add('general.timezone', 'America/Bogota');
        }
    }
};

