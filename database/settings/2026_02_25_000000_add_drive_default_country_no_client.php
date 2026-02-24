<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->addIfNotExists('general.drive_default_country_no_client', '');
    }

    protected function addIfNotExists(string $key, $defaultValue): void
    {
        $name = str_replace('general.', '', $key);
        $exists = DB::table('settings')
            ->where('group', 'general')
            ->where('name', $name)
            ->exists();

        if (!$exists) {
            $this->migrator->add($key, $defaultValue);
        }
    }
};
