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
        'status',
        'total_professional_fees',
        'total_invima_fees',
        'total_loans',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_professional_fees' => 'decimal:2',
            'total_invima_fees' => 'decimal:2',
            'total_loans' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_id');
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }
}
