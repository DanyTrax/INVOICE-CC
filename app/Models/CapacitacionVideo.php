<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapacitacionVideo extends Model
{
    protected $table = 'capacitacion_videos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'drive_file_id',
        'drive_folder_id',
        'nombre_archivo',
        'orden',
        'created_by',
    ];

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(CapacitacionCompletion::class, 'capacitacion_video_id');
    }
}
