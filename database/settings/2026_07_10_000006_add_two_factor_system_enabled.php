<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $exists = DB::table('settings')
            ->where('group', 'general')
            ->where('name', 'two_factor_system_enabled')
            ->exists();

        if (! $exists) {
            $this->migrator->add('general.two_factor_system_enabled', true);
        }
    }
};
