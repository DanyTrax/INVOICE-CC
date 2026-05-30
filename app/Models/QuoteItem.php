<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id',
        'service_id',
        'service_label',
        'item_position',
        'row_id',
        'service_type_id',
        'raa_code',
        'franquicia',
        'centro_costos',
        'contacto',
        'previous_license',
        'description',
        'scope',
        'fee_value',
        'invima_rate_code',
        'invima_rate_value',
    ];

    protected function casts(): array
    {
        return [
            'fee_value' => 'decimal:2',
            'invima_rate_value' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function process(): HasOne
    {
        return $this->hasOne(Process::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'quote_item_id');
    }

    /**
     * Enlace informativo a solicitud/ciclo (no modifica datos de la cotización).
     *
     * @return array{url: string, label: string, title: string}|null
     */
    public function resolveLinkedSolicitudButton(): ?array
    {
        if (! $this->id) {
            return null;
        }

        $itemId = (int) $this->id;

        $submissions = $this->relationLoaded('submissions')
            ? $this->submissions
            : $this->submissions()->with('process')->get();

        $submission = $submissions
            ->filter(fn ($s) => (int) ($s->quote_item_id ?? 0) === $itemId && $s->process)
            ->sortByDesc('id')
            ->first();

        if ($submission?->process) {
            $process = $submission->process;
            $label = 'Sol. '.$process->displayReference();
            $roots = $process->relationLoaded('submissions')
                ? $process->submissions->whereNull('parent_id')->sortBy('id')->values()
                : $process->submissions()->whereNull('parent_id')->orderBy('id')->get();
            $rootForItem = $roots->first(fn ($r) => (int) ($r->quote_item_id ?? 0) === $itemId);
            if ($rootForItem) {
                $pos = $roots->search(fn ($r) => $r->id === $rootForItem->id);
                if ($pos !== false) {
                    $label .= ' · Ciclo '.($pos + 1);
                }
            }

            return [
                'url' => route('admin.processes.show', $process),
                'label' => $label,
                'title' => 'Ver solicitud vinculada a este ítem (solo referencia)',
            ];
        }

        $process = $this->relationLoaded('process') ? $this->process : $this->process()->first();
        if ($process && (int) ($process->quote_item_id ?? 0) === $itemId) {
            return [
                'url' => route('admin.processes.show', $process),
                'label' => 'Sol. '.$process->displayReference(),
                'title' => 'Ver solicitud vinculada a este ítem (solo referencia)',
            ];
        }

        return null;
    }
}
