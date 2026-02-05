<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulatoryEvent extends Model
{
    protected $fillable = [
        'submission_id',
        'event_type',
        'document_number',
        'event_date',
        'notification_date',
        'due_date',
        'resolution_key',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'notification_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
