<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptPrice extends Model
{
    protected $fillable = [
        'concept_id',
        'category',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function concept(): BelongsTo
    {
        return $this->belongsTo(Concept::class);
    }
}
