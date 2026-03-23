<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConceptCatalog extends Model
{
    protected $fillable = [
        'name',
        'scope',
        'default_fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function proposalItems(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
