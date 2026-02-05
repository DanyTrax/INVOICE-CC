<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'default_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }
}
