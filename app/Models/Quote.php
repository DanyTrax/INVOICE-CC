<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'client_id',
        'consecutive',
        'date',
        'currency',
        'exchange_rate',
        'show_prev_license_column',
        'show_raa_column',
        'status',
        'cancellation_note',
        'total_professional_fees',
        'total_invima_fees',
        'total_loans',
        'apply_tax',
        'tax_percentage',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_professional_fees' => 'decimal:2',
            'total_invima_fees' => 'decimal:2',
            'total_loans' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'show_prev_license_column' => 'boolean',
            'show_raa_column' => 'boolean',
            'apply_tax' => 'boolean',
            'tax_percentage' => 'decimal:2',
        ];
    }

    /**
     * Subtotal (honorarios + préstamos + tasas INVIMA).
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->total_professional_fees + (float) $this->total_loans + (float) $this->total_invima_fees;
    }

    /**
     * Monto del IVA cuando apply_tax es true.
     */
    public function getTaxAmountAttribute(): float
    {
        if (!$this->apply_tax || $this->tax_percentage === null) {
            return 0.0;
        }
        return round($this->subtotal * (float) $this->tax_percentage / 100, 2);
    }

    /**
     * Total con IVA cuando apply_tax es true; si no, igual al subtotal.
     */
    public function getTotalWithTaxAttribute(): float
    {
        return round($this->subtotal + $this->tax_amount, 2);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_id');
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
