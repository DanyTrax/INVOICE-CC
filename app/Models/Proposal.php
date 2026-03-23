<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    public const STATUS_PENDIENTE = 'Pendiente';
    public const STATUS_APROBADA = 'Aprobada';

    protected $fillable = [
        'client_id',
        'consecutive',
        'date',
        'currency',
        'exchange_rate',
        'status',
        'pdf_footer',
        'total_professional_fees',
        'apply_tax',
        'tax_percentage',
        'apply_bank_fee',
        'bank_fee_value',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'exchange_rate' => 'decimal:6',
            'total_professional_fees' => 'decimal:2',
            'apply_tax' => 'boolean',
            'tax_percentage' => 'decimal:2',
            'apply_bank_fee' => 'boolean',
            'bank_fee_value' => 'decimal:2',
        ];
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->total_professional_fees;
    }

    public function getTaxAmountAttribute(): float
    {
        if (!$this->apply_tax || $this->tax_percentage === null) {
            return 0.0;
        }

        return round($this->subtotal * (float) $this->tax_percentage / 100, 2);
    }

    public function getBankFeeAmountAttribute(): float
    {
        if (!$this->apply_bank_fee || $this->bank_fee_value === null) {
            return 0.0;
        }

        return round((float) $this->bank_fee_value, 2);
    }

    public function getTotalWithTaxAttribute(): float
    {
        return round($this->subtotal + $this->tax_amount + $this->bank_fee_amount, 2);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_id');
    }

    public function proposalItems(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }
}
