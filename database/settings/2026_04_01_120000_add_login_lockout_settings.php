<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'login_lockout_enabled' => true,
            'login_max_failed_attempts' => 5,
            'login_lockout_duration_minutes' => 30,
        ];

        foreach ($defaults as $name => $value) {
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
