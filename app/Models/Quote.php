<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'client_id',
        'contact_user_id',
        'consecutive',
        'date',
        'currency',
        'exchange_rate',
        'show_prev_license_column',
        'show_raa_column',
        'show_service_type_column',
        'show_description_column',
        'show_row_id_column',
        'show_franquicia_column',
        'show_centro_costos_column',
        'show_contacto_column',
        'status',
        'cancellation_note',
        'pdf_footer',
        'pdf_body_html',
        'pdf_side_note_html',
        'show_pdf_side_note',
        'show_pdf_footer',
        'total_professional_fees',
        'total_invima_fees',
        'apply_tax',
        'tax_percentage',
        'apply_bank_fee',
        'bank_fee_value',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_professional_fees' => 'decimal:2',
            'total_invima_fees' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'show_prev_license_column' => 'boolean',
            'show_raa_column' => 'boolean',
            'show_service_type_column' => 'boolean',
            'show_description_column' => 'boolean',
            'show_row_id_column' => 'boolean',
            'show_franquicia_column' => 'boolean',
            'show_centro_costos_column' => 'boolean',
            'show_contacto_column' => 'boolean',
            'apply_tax' => 'boolean',
            'tax_percentage' => 'decimal:2',
            'apply_bank_fee' => 'boolean',
            'bank_fee_value' => 'decimal:2',
            'show_pdf_side_note' => 'boolean',
            'show_pdf_footer' => 'boolean',
        ];
    }

    /**
     * Subtotal (honorarios + tasas INVIMA).
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->total_professional_fees + (float) $this->total_invima_fees;
    }

    /**
     * Monto del IVA cuando apply_tax es true.
     */
    public function getTaxAmountAttribute(): float
    {
        if (! $this->apply_tax || $this->tax_percentage === null) {
            return 0.0;
        }

        return round($this->subtotal * (float) $this->tax_percentage / 100, 2);
    }

    /**
     * Gasto bancario efectivo (solo si apply_bank_fee y valor definido).
     */
    public function getBankFeeAmountAttribute(): float
    {
        if (! $this->apply_bank_fee || $this->bank_fee_value === null) {
            return 0.0;
        }

        return round((float) $this->bank_fee_value, 2);
    }

    /**
     * Total final: subtotal + IVA (si aplica) + Gasto bancario (si aplica).
     */
    public function getTotalWithTaxAttribute(): float
    {
        return round($this->subtotal + $this->tax_amount + $this->bank_fee_amount, 2);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_id');
    }

    /**
     * Persona de contacto (usuario cliente) opcional para esta cotización.
     * Si está definida, se usa como {{cliente}} en el PDF; si no, se usa la empresa.
     */
    public function contactUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contact_user_id');
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * Procesos vinculados a esta cotización por quote_id (organización en acordeones).
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }
}
