<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'process_id',
        'parent_id',
        'submission_type',
        'filing_number',
        'tracking_id',
        'filing_date',
        'status',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'filing_date' => 'date',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Submission::class, 'parent_id');
    }

    public function regulatoryEvents(): HasMany
    {
        return $this->hasMany(RegulatoryEvent::class, 'submission_id');
    }
}
