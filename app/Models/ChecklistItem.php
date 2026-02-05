<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $fillable = [
        'process_id',
        'document_name',
        'is_translation_required',
        'status',
        'observation_agent',
        'observation_client',
    ];

    protected function casts(): array
    {
        return [
            'is_translation_required' => 'boolean',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}
