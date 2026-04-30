<?php

namespace App\Exports;

use App\Models\Process;
use App\Models\Quote;
use App\Models\RegulatoryEvent;
use App\Models\User;
use App\Services\ProcessAccessService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GeneralProcessExport implements FromQuery, WithHeadings, WithMapping
{
    /** @var array<string, mixed> */
    protected array $filters;

    public function __construct(
        array $filters = [],
        protected ?User $forUser = null
    ) {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Process::query()->with([
            'client',
            'quote',
            'quoteItem.quote',
            'quoteItem.serviceType',
            'quoteItem',
            'serviceType',
            'submissions.regulatoryEvents',
        ]);

        if (! empty($this->filters['client_id'])) {
            $query->where('client_id', $this->filters['client_id']);
        }

        $step = isset($this->filters['step']) ? (int) $this->filters['step'] : null;
        if ($step !== null && $step >= 1 && $step <= 5) {
            $query->whereStep($step);
        } elseif (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('updated_at', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->whereDate('updated_at', '<=', $this->filters['date_to']);
        }

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('product_reference', 'like', "%{$search}%")
                    ->orWhere('expediente_invima', 'like', "%{$search}%")
                    ->orWhere('solicitud_code', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('quote', fn ($q2) => $q2->where('consecutive', 'like', "%{$search}%"))
                    ->orWhereHas('quoteItem.quote', fn ($q2) => $q2->where('consecutive', 'like', "%{$search}%"));
            });
        }

        if (! empty($this->filters['quote_id'])) {
            $qid = (int) $this->filters['quote_id'];
            if (Quote::where('id', $qid)->exists()) {
                $query->whereLinkedToQuote($qid);
            }
        }

        if ($this->forUser) {
            app(ProcessAccessService::class)->scopeProcessesForUser($query, $this->forUser);
        }

        return $query->orderBy('updated_at', 'desc');
    }

    /**
     * @param  Process  $process
     */
    public function map($process): array
    {
        $fechaSolicitud = $process->quote?->date ?? $process->quoteItem?->quote?->date;
        $fechaSometimiento = $process->submissions->sortByDesc('submission_date')->first()?->submission_date
            ?? $process->submissions->sortByDesc('fecha_radicacion')->first()?->fecha_radicacion;

        $radicado = $process->expediente_invima;
        if ($radicado === null) {
            $firstSub = $process->submissions->first();
            $radicado = $firstSub?->radicado_invima ?? $firstSub?->submission_code ?? '';
        }

        $autoEvent = $process->submissions->flatMap->regulatoryEvents
            ->where('event_type', RegulatoryEvent::EVENT_TYPE_AUTO)
            ->sortByDesc('event_date')
            ->first();
        $resolucionEvent = $process->submissions->flatMap->regulatoryEvents
            ->where('event_type', RegulatoryEvent::EVENT_TYPE_RESOLUCION)
            ->sortByDesc('event_date')
            ->first();

        $valorTasa = $process->quoteItem?->invima_rate_value;
        if ($valorTasa !== null) {
            $valorTasa = (string) round((float) $valorTasa, 2);
        } else {
            $valorTasa = '';
        }

        return [
            $process->displayReference(),
            $fechaSolicitud ? $fechaSolicitud->format('d/m/Y') : '',
            $process->client?->name ?? '',
            $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '',
            $process->product_reference ?? '',
            $fechaSometimiento ? ($fechaSometimiento instanceof Carbon ? $fechaSometimiento->format('d/m/Y') : Carbon::parse($fechaSometimiento)->format('d/m/Y')) : '',
            $radicado ?? '',
            $autoEvent?->document_number ?? '',
            $resolucionEvent?->document_number ?? '',
            $resolucionEvent?->resolution_key ?? '',
            $valorTasa,
        ];
    }

    public function headings(): array
    {
        return [
            'Código solicitud',
            'Fecha Solicitud',
            'Cliente',
            'Tipo Trámite',
            'Producto',
            'Fecha Sometimiento',
            'Radicado',
            'Auto',
            'Resolución',
            'Llave',
            'Valor Tasa',
        ];
    }
}
