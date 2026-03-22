<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RegulatoryEvent;
use App\Models\Submission;

class Process extends Model
{
    public const STATUS_RECOLECCION = 'Recolección';
    public const STATUS_RADICADO = 'Radicado';
    public const STATUS_EN_REQUERIMIENTO = 'En Requerimiento';
    public const STATUS_FINALIZADO = 'Finalizado';

    public static function statuses(): array
    {
        return [
            self::STATUS_RECOLECCION,
            self::STATUS_RADICADO,
            self::STATUS_EN_REQUERIMIENTO,
            self::STATUS_FINALIZADO,
        ];
    }

    /** Pasos del flujo del expediente para mostrar en qué etapa está. */
    public const STEP_RECOLECCION = 1;
    public const STEP_SOMETIMIENTO = 2;
    public const STEP_RADICADO = 3;
    public const STEP_AUTO = 4;
    public const STEP_FINALIZADO = 5;

    public static function stepLabels(): array
    {
        return [
            self::STEP_RECOLECCION => 'Recolección',
            self::STEP_SOMETIMIENTO => 'Sometimiento',
            self::STEP_RADICADO => 'Radicado',
            self::STEP_AUTO => 'AUTO',
            self::STEP_FINALIZADO => 'Finalizado',
        ];
    }

    /**
     * Etiquetas para filtros (monitor): aclara el paso AUTO.
     */
    public static function stepFilterLabels(): array
    {
        $l = self::stepLabels();
        $l[self::STEP_AUTO] = 'AUTO (Recolección, Sometimiento, Radicado…)';

        return $l;
    }

    /**
     * Ya existe al menos un requerimiento AUTO registrado en la línea de tiempo.
     */
    public function hasAutoRegulatoryEvent(): bool
    {
        if ($this->relationLoaded('submissions')) {
            foreach ($this->submissions as $s) {
                if ($s->relationLoaded('regulatoryEvents')) {
                    if ($s->regulatoryEvents->contains('event_type', RegulatoryEvent::EVENT_TYPE_AUTO)) {
                        return true;
                    }
                } else {
                    if ($s->regulatoryEvents()->where('event_type', RegulatoryEvent::EVENT_TYPE_AUTO)->exists()) {
                        return true;
                    }
                }
            }

            return false;
        }

        return RegulatoryEvent::query()
            ->where('event_type', RegulatoryEvent::EVENT_TYPE_AUTO)
            ->whereHas('submission', fn ($q) => $q->where('process_id', $this->id))
            ->exists();
    }

    /**
     * Expediente con AUTO registrado y aún no finalizado (todas las sub-fases del trámite AUTO).
     */
    public function isInAutoPipeline(): bool
    {
        if ($this->status === self::STATUS_FINALIZADO) {
            return false;
        }

        return $this->hasAutoRegulatoryEvent();
    }

    /**
     * Alcance del filtro "Paso: AUTO" en monitor y conteo del dashboard.
     */
    public function scopeWhereAutoPipeline($query)
    {
        return $query->where('status', '!=', self::STATUS_FINALIZADO)
            ->whereHas('submissions.regulatoryEvents', function ($q) {
                $q->where('event_type', RegulatoryEvent::EVENT_TYPE_AUTO);
            });
    }

    /**
     * Etiqueta detallada del trámite cuando ya hubo AUTO: AUTO (Recolección), AUTO (Sometimiento), etc.
     */
    public function getCurrentStepDetailedLabel(): string
    {
        $last = $this->relationLoaded('submissions')
            ? $this->submissions->sortByDesc('id')->first()
            : $this->submissions()->orderByDesc('id')->first();

        if (!$last) {
            return 'AUTO (Recolección)';
        }

        return match ($last->status) {
            Submission::STATUS_EN_REQUERIMIENTO => 'AUTO (Recolección)',
            Submission::STATUS_APROBADO => 'AUTO (Finalizado)',
            Submission::STATUS_RADICADO => 'AUTO (Radicado)',
            Submission::STATUS_PENDIENTE => $last->isAutoFollowUpCycle()
                ? 'AUTO (Sometimiento)'
                : 'Sometimiento',
            Submission::STATUS_RECHAZADO => 'AUTO (Recolección)',
            default => 'AUTO (Recolección)',
        };
    }

    /**
     * Texto para "Estado" / paso resaltado: mezcla estado del expediente con fase AUTO si aplica.
     */
    public function getDisplayStatusLabel(): string
    {
        if ($this->hasAutoRegulatoryEvent()) {
            return $this->getCurrentStepDetailedLabel();
        }

        return $this->status;
    }

    /**
     * Etiqueta del paso actual en la barra (misma lógica que el resumen).
     */
    public function getDisplayLabelForCurrentFlowStep(): string
    {
        if ($this->hasAutoRegulatoryEvent()) {
            return $this->getCurrentStepDetailedLabel();
        }

        return $this->getCurrentStepLabel();
    }

    /**
     * Devuelve el paso actual del expediente (1-5) según el último sometimiento.
     */
    public function getCurrentStep(): int
    {
        $last = $this->relationLoaded('submissions')
            ? $this->submissions->sortByDesc('id')->first()
            : $this->submissions()->orderByDesc('id')->first();
        if (!$last) {
            return self::STEP_RECOLECCION;
        }
        return match ($last->status) {
            \App\Models\Submission::STATUS_APROBADO => self::STEP_FINALIZADO,
            \App\Models\Submission::STATUS_EN_REQUERIMIENTO => self::STEP_AUTO,
            \App\Models\Submission::STATUS_RADICADO => self::STEP_RADICADO,
            \App\Models\Submission::STATUS_PENDIENTE => self::STEP_SOMETIMIENTO,
            default => self::STEP_RECOLECCION, // Rechazado u otro
        };
    }

    /**
     * Etiqueta del paso actual (ej. "Sometimiento").
     */
    public function getCurrentStepLabel(): string
    {
        $labels = self::stepLabels();
        return $labels[$this->getCurrentStep()] ?? 'Recolección';
    }

    /**
     * Filtra procesos por paso del flujo (1-5). Para listados y Monitor.
     */
    public function scopeWhereStep($query, int $step): void
    {
        $subLastStatus = '(SELECT status FROM submissions WHERE process_id = processes.id ORDER BY id DESC LIMIT 1)';
        match ($step) {
            self::STEP_RECOLECCION => $query->where(function ($q) use ($subLastStatus) {
                $q->whereDoesntHave('submissions')
                    ->orWhereRaw($subLastStatus . ' = ?', [\App\Models\Submission::STATUS_RECHAZADO]);
            }),
            self::STEP_SOMETIMIENTO => $query->whereRaw($subLastStatus . ' = ?', [\App\Models\Submission::STATUS_PENDIENTE]),
            self::STEP_RADICADO => $query->where('status', self::STATUS_RADICADO),
            self::STEP_AUTO => $query->whereAutoPipeline(),
            self::STEP_FINALIZADO => $query->where('status', self::STATUS_FINALIZADO),
            default => null,
        };
    }

    protected $fillable = [
        'quote_item_id',
        'quote_id',
        'client_id',
        'service_type_id',
        'product_reference',
        'email_name',
        'status',
        'expediente_invima',
        'drive_folder_id',
        'drive_folder_url',
    ];

    protected $attributes = [
        'status' => self::STATUS_RECOLECCION,
    ];

    public function quoteItem(): BelongsTo
    {
        return $this->belongsTo(QuoteItem::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'process_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'process_id');
    }

    public function processDocuments(): HasMany
    {
        return $this->hasMany(ProcessDocument::class, 'process_id');
    }
}
