<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacitacionCompletion extends Model
{
    protected $table = 'capacitacion_completions';

    protected $fillable = [
        'capacitacion_video_id',
        'user_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function capacitacionVideo(): BelongsTo
    {
        return $this->belongsTo(CapacitacionVideo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
