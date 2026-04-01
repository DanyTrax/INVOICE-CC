<?php

use App\Support\LegalPageDefaults;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $rows = [
            'legal_privacy_html' => LegalPageDefaults::privacyHtml(),
            'legal_terms_html' => LegalPageDefaults::termsHtml(),
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
