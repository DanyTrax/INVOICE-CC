<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $table = 'brand_settings';

    protected $fillable = [
        'company_name',
        'nit',
        'address',
        'city',
        'phone',
        'email',
        'logo_path',
        'bank_name',
        'bank_account_type',
        'bank_account_number',
        'support_email',
        'treasurer_signature_path',
        'treasurer_signature_title',
        'invoice_email_subject',
        'invoice_email_body',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'company_name' => config('app.name', 'Dashboard de Recaudos'),
            'invoice_email_subject' => 'Cuenta de cobro — {{numero}}',
            'invoice_email_body' => '<p>Estimado(a) {{nombre}},</p><p>Adjuntamos su cuenta de cobro correspondiente al concepto <strong>{{concepto}}</strong>.</p><p>Quedamos atentos a cualquier inquietud.</p>',
        ]);
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return asset('storage/'.$this->logo_path);
    }

    public function logoBase64(): ?string
    {
        return $this->imageToBase64($this->logo_path);
    }

    public function treasurerSignatureBase64(): ?string
    {
        return $this->imageToBase64($this->treasurer_signature_path);
    }

    private function imageToBase64(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolute = Storage::disk('public')->path($path);
        $mime = mime_content_type($absolute) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolute));
    }
}
