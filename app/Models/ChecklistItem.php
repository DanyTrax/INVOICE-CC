<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    public const STATUS_PENDIENTE = 'Pendiente';
    public const STATUS_RECIBIDO = 'Recibido';
    public const STATUS_TRADUCCION = 'Traducción';
    public const STATUS_APROBADO = 'Aprobado';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDIENTE,
            self::STATUS_RECIBIDO,
            self::STATUS_TRADUCCION,
            self::STATUS_APROBADO,
        ];
    }

    protected $fillable = [
        'process_id',
        'document_name',
        'status',
        'is_translation_required',
        'observation_agent',
        'observation_client',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDIENTE,
    ];

    protected function casts(): array
    {
        return [
            'is_translation_required' => 'boolean',
        ];
    }

    /**
     * Un Process tiene muchos ChecklistItems.
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}
