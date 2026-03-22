@foreach($processes as $process)
    @php
        $consecutivo = $process->quote?->consecutive ?? $process->quoteItem?->quote?->consecutive ?? '—';
        $tipoTramite = $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '—';
        $radicadoBase = $process->expediente_invima ?? $process->submissions->first()?->radicado_invima ?? $process->submissions->first()?->submission_code ?? null;
        $radicadoId = 'Expediente #' . $process->id;
        if ($radicadoBase) {
            $radicadoId .= ' · ' . $radicadoBase;
        }
        $fechaUltimo = null;
        foreach ($process->submissions as $sub) {
            foreach ($sub->regulatoryEvents as $ev) {
                $d = $ev->event_date ?? $ev->notification_date;
                if ($d && ($fechaUltimo === null || $d->gt($fechaUltimo))) {
                    $fechaUltimo = $d;
                }
            }
            if ($sub->fecha_radicacion && ($fechaUltimo === null || $sub->fecha_radicacion->gt($fechaUltimo))) {
                $fechaUltimo = $sub->fecha_radicacion;
            }
        }
        if ($fechaUltimo === null) {
            $fechaUltimo = $process->updated_at;
        }
        $paso = $process->getCurrentStep();
        $stepStyles = [1 => 'bg-gray-100 text-gray-800', 2 => 'bg-teal-100 text-teal-800', 3 => 'bg-blue-100 text-blue-800', 4 => 'bg-yellow-100 text-yellow-800', 5 => 'bg-green-100 text-green-800'];
        $estadoLabel = $process->getDisplayStatusLabel();
        $stepClass = str_starts_with($estadoLabel, 'AUTO')
            ? 'bg-amber-100 text-amber-900'
            : ($stepStyles[$paso] ?? 'bg-gray-100 text-gray-800');
    @endphp
    <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
        <td class="px-4 py-3 text-sm text-gray-900">{{ $consecutivo }}</td>
        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $process->client->name ?? '—' }}</td>
        <td class="px-4 py-3 text-sm text-gray-700">{{ $tipoTramite }}</td>
        <td class="px-4 py-3 text-sm text-gray-700">{{ $process->product_reference ?? '—' }}</td>
        <td class="px-4 py-3 text-sm text-gray-700">{{ $radicadoId }}</td>
        <td class="px-4 py-3">
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $stepClass }}" title="Paso {{ $paso }}">{{ $estadoLabel }}</span>
        </td>
        <td class="px-4 py-3 text-sm text-gray-600">{{ $fechaUltimo ? (\Carbon\Carbon::parse($fechaUltimo)->format('d/m/Y')) : '—' }}</td>
        <td class="px-4 py-3">
            <a href="{{ route('admin.processes.show', $process) }}" class="text-teal-600 hover:text-teal-800 font-medium text-sm mr-2">
                <i class="fas fa-eye mr-1"></i> Ver
            </a>
            <a href="{{ route('admin.processes.show', $process) }}" class="text-gray-600 hover:text-gray-800 font-medium text-sm">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
        </td>
    </tr>
@endforeach
@if($processes->isEmpty())
    <tr>
        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No hay expedientes con los filtros aplicados.</td>
    </tr>
@endif
