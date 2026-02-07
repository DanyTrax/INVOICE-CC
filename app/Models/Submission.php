<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    public const STATUS_PENDIENTE = 'Pendiente';
    public const STATUS_EN_REQUERIMIENTO = 'En Requerimiento';
    public const STATUS_APROBADO = 'Aprobado';
    public const STATUS_RECHAZADO = 'Rechazado';

    /** @deprecated Use STATUS_PENDIENTE */
    public const STATUS_EN_ESTUDIO = 'Pendiente';
    /** @deprecated Use STATUS_EN_REQUERIMIENTO */
    public const STATUS_REQUERIDO = 'En Requerimiento';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDIENTE,
            self::STATUS_EN_REQUERIMIENTO,
            self::STATUS_APROBADO,
            self::STATUS_RECHAZADO,
        ];
    }

    protected $fillable = [
        'process_id',
        'parent_id',
        'submission_date',
        'submission_code',
        'radicado_invima',
        'tracking_id',
        'fecha_radicacion',
        'status',
        'submission_type',
        'payment_reference',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDIENTE,
    ];

    protected function casts(): array
    {
        return [
            'submission_date' => 'datetime',
            'fecha_radicacion' => 'date',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /** Relación recursiva: sometimiento padre (rechazo anterior). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'parent_id');
    }

    /** Relación recursiva: intentos hijos (historial de intentos tras rechazos). */
    public function children(): HasMany
    {
        return $this->hasMany(Submission::class, 'parent_id');
    }

    public function regulatoryEvents(): HasMany
    {
        return $this->hasMany(RegulatoryEvent::class, 'submission_id');
    }
}
