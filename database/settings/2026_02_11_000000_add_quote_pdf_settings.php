<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->addIfNotExists('general.quote_pdf_header_subtitle', 'RAMS - Regulatory Affairs Management System');
        $this->addIfNotExists('general.quote_pdf_footer_text', '');
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
