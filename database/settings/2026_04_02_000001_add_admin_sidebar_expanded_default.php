<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $exists = DB::table('settings')
            ->where('group', 'general')
            ->where('name', 'admin_sidebar_expanded_default')
            ->exists();

        if (! $exists) {
            $this->migrator->add('general.admin_sidebar_expanded_default', false);
        }
    }
};
