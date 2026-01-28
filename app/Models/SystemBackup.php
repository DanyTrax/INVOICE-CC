<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBackup extends Model
{
    protected $fillable = [
        'name',
        'drive_file_id',
        'size_bytes',
        'created_by_id',
        'type',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

