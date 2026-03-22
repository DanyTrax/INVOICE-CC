<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    public const STATUS_PENDIENTE = 'Pendiente';
    public const STATUS_RADICADO = 'Radicado';
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
            self::STATUS_RADICADO,
            self::STATUS_EN_REQUERIMIENTO,
            self::STATUS_APROBADO,
            self::STATUS_RECHAZADO,
        ];
    }

    protected $fillable = [
        'process_id',
        'parent_id',
        'quote_id',
        'quote_item_id',
        'submission_date',
        'submission_code',
        'radicado_invima',
        'tracking_id',
        'fecha_radicacion',
        'status',
        'rejection_observation',
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

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function quoteItem(): BelongsTo
    {
        return $this->belongsTo(QuoteItem::class);
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

    /**
     * Raíz del "ciclo" (intento principal): el sometimiento sin padre en la cadena de rechazos/subsanaciones.
     */
    public function rootSubmission(): self
    {
        $s = $this;
        while ($s->parent_id) {
            $s = $s->parent;
        }

        return $s;
    }

    /**
     * Número de ciclo (1 = primer sometimiento raíz, 2 = subsanación tras AUTO, etc.).
     */
    public function cycleNumber(): int
    {
        $root = $this->rootSubmission();
        $ids = static::query()
            ->where('process_id', $root->process_id)
            ->whereNull('parent_id')
            ->orderByRaw('COALESCE(submission_date, created_at)')
            ->orderBy('id')
            ->pluck('id');
        $pos = $ids->search($root->id);

        return $pos !== false ? (int) $pos + 1 : 1;
    }

    /**
     * Ciclo 2+ = trámite de subsanación tras un requerimiento AUTO (solo Resolución al volver a Radicado).
     */
    public function isAutoFollowUpCycle(): bool
    {
        return $this->cycleNumber() >= 2;
    }
}
