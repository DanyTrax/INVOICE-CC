<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        if (! $last) {
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
        if (! $last) {
            return self::STEP_RECOLECCION;
        }

        return match ($last->status) {
            Submission::STATUS_APROBADO => self::STEP_FINALIZADO,
            Submission::STATUS_EN_REQUERIMIENTO => self::STEP_AUTO,
            Submission::STATUS_RADICADO => self::STEP_RADICADO,
            Submission::STATUS_PENDIENTE => self::STEP_SOMETIMIENTO,
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
     * Expedientes vinculados a una cotización: asignación directa, por ítem, o por ciclos (sometimientos).
     */
    public function scopeWhereLinkedToQuote($query, int $quoteId): void
    {
        $itemIds = QuoteItem::where('quote_id', $quoteId)->pluck('id');
        $query->where(function ($q) use ($quoteId, $itemIds) {
            $q->where('quote_id', $quoteId);
            if ($itemIds->isNotEmpty()) {
                $q->orWhereIn('quote_item_id', $itemIds);
            }
            $q->orWhereHas('submissions', function ($sq) use ($quoteId, $itemIds) {
                $sq->where('quote_id', $quoteId);
                if ($itemIds->isNotEmpty()) {
                    $sq->orWhereIn('quote_item_id', $itemIds);
                }
            });
        });
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
                    ->orWhereRaw($subLastStatus.' = ?', [Submission::STATUS_RECHAZADO]);
            }),
            self::STEP_SOMETIMIENTO => $query->whereRaw($subLastStatus.' = ?', [Submission::STATUS_PENDIENTE]),
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
        'request_sequence',
        'solicitud_code',
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

    /**
     * Código visible de la solicitud (ej. PG-001). Inmutable tras creación. Si falta, se usa el id interno.
     */
    public function displayReference(): string
    {
        if (! empty($this->solicitud_code)) {
            return (string) $this->solicitud_code;
        }

        return (string) $this->id;
    }

    /**
     * Nombre de carpeta en Google Drive: código con siglas + descripción (ej. JJMED-001 – MODIFICACION LEGAL).
     */
    public function driveFolderName(): string
    {
        $descriptor = $this->quoteItem?->serviceType?->name
            ?? $this->serviceType?->name
            ?? ($this->product_reference ? Str::limit(trim($this->product_reference), 80) : null)
            ?? 'Sin nombre';

        return $this->displayReference().' – '.$descriptor;
    }

    /**
     * Crea un proceso asignando correlativo y código bajo el cliente (transacción + bloqueo).
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws \RuntimeException Si la empresa no tiene siglas.
     */
    public static function createWithSolicitudCode(array $attributes): self
    {
        $clientId = (int) ($attributes['client_id'] ?? 0);
        if ($clientId < 1) {
            throw new \InvalidArgumentException('client_id es obligatorio.');
        }

        return DB::transaction(function () use ($attributes, $clientId) {
            $company = Company::query()->whereKey($clientId)->lockForUpdate()->first();
            if (! $company) {
                throw new \InvalidArgumentException('Empresa no encontrada.');
            }

            $raw = (string) $company->code_abbreviation;
            $prefix = strtoupper(preg_replace('/[^A-Za-z]/', '', $raw) ?? '');
            if ($prefix === '' || mb_strlen($prefix) < 2) {
                throw new \RuntimeException('La empresa debe tener siglas (2–10 letras) para crear solicitudes. Edite la empresa en el directorio.');
            }

            $prefix = mb_substr($prefix, 0, 10);

            $max = (int) static::query()->where('client_id', $clientId)->max('request_sequence');
            $seq = $max + 1;
            $attributes['request_sequence'] = $seq;
            $width = max(3, strlen((string) $seq));
            $attributes['solicitud_code'] = $prefix.'-'.str_pad((string) $seq, $width, '0', STR_PAD_LEFT);

            return static::create($attributes);
        });
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

    /**
     * Agentes/usuarios asignados al expediente (con permisos por expediente en el pivote).
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'process_user')
            ->withPivot(['can_feed_timeline', 'can_manage_documents'])
            ->withTimestamps();
    }

    /**
     * ¿Hay al menos un usuario en process_user? (Útil para UI/admin; la visibilidad de agentes exige estar en la lista.)
     */
    public function hasExplicitAssignments(): bool
    {
        if ($this->relationLoaded('assignedUsers')) {
            return $this->assignedUsers->isNotEmpty();
        }

        return $this->assignedUsers()->exists();
    }
}
