<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalItem extends Model
{
    protected $fillable = [
        'proposal_id',
        'concept_catalog_id',
        'item_position',
        'concept',
        'scope',
        'fee_value',
    ];

    protected function casts(): array
    {
        return [
            'fee_value' => 'decimal:2',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function conceptCatalog(): BelongsTo
    {
        return $this->belongsTo(ConceptCatalog::class);
    }
}
