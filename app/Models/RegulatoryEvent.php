<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulatoryEvent extends Model
{
    public const EVENT_TYPE_AUTO = 'AUTO';
    public const EVENT_TYPE_RESOLUCION = 'RESOLUCION';

    /** @return array<string> */
    public static function eventTypes(): array
    {
        return [self::EVENT_TYPE_AUTO, self::EVENT_TYPE_RESOLUCION];
    }

    protected $fillable = [
        'submission_id',
        'saved_by_user_id',
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

    public function savedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }
}
