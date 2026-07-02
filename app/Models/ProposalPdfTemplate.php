<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalPdfTemplate extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'letterhead_path',
        'letterhead_drive_id',
        'header_company_name',
        'header_nit',
        'header_subtitle',
        'body_html',
        'side_note_html',
        'closing_footer_html',
        'footer_text',
        'signature_name',
        'signature_position',
        'signature_image_path',
        'signature_image_drive_id',
        'signature_image_height_px',
        'signature_name_font_size',
        'signature_position_font_size',
        'signature_margin_top_px',
        'letterhead_footer_reserve_mm',
        'doc_title_text',
        'doc_title_font_size',
        'doc_title_bold',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'doc_title_bold' => 'boolean',
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
