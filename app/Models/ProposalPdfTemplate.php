<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalPdfTemplate extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'header_company_name',
        'header_nit',
        'header_subtitle',
        'body_html',
        'footer_text',
        'signature_name',
        'signature_position',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public static function getDefault(): ?self
    {
        $t = static::where('is_default', true)->first();
        if ($t) {
            return $t;
        }

        return static::orderBy('id')->first();
    }
}
