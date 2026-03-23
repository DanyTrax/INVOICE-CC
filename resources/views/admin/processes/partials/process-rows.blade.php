@foreach($processes as $process)
    @php
        $tipoTramite = $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '—';
        $fechaUltimoGuardado = $process->updated_at;
        $paso = $process->getCurrentStep();
        $stepStyles = [1 => 'bg-gray-100 text-gray-800', 2 => 'bg-teal-100 text-teal-800', 3 => 'bg-blue-100 text-blue-800', 4 => 'bg-yellow-100 text-yellow-800', 5 => 'bg-green-100 text-green-800'];
        $estadoLabel = $process->getDisplayStatusLabel();
        $stepClass = str_starts_with($estadoLabel, 'AUTO')
            ? 'bg-amber-100 text-amber-900'
            : ($stepStyles[$paso] ?? 'bg-gray-100 text-gray-800');
    @endphp
    <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
        <td class="px-4 py-3 text-sm text-gray-900 font-mono" title="{{ $process->expediente_invima ? 'INVIMA: '.$process->expediente_invima.' · ' : '' }}ID expediente: {{ $process->id }}">{{ $process->id }}</td>
        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $process->client->name ?? '—' }}</td>
        <td class="px-4 py-3 text-sm text-gray-700">{{ $tipoTramite }}</td>
        <td class="px-4 py-3 text-sm text-gray-700">{{ $process->product_reference ?? '—' }}</td>
        <td class="px-4 py-3">
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $stepClass }}" title="Paso {{ $paso }}">{{ $estadoLabel }}</span>
        </td>
        <td class="px-4 py-3 text-sm text-gray-600">{{ $fechaUltimoGuardado ? $fechaUltimoGuardado->format('d/m/Y H:i') : '—' }}</td>
        <td class="px-4 py-3 align-top max-w-[11rem]">
            @if($process->relationLoaded('assignedUsers') && $process->assignedUsers->isNotEmpty())
                <p class="text-xs text-gray-800 truncate" title="{{ $process->assignedUsers->pluck('name')->join(', ') }}">
                    {{ $process->assignedUsers->take(2)->pluck('name')->join(', ') }}@if($process->assignedUsers->count() > 2) +{{ $process->assignedUsers->count() - 2 }}@endif
                </p>
            @else
                <span class="text-xs text-gray-400">Sin asignar</span>
            @endif
            @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                <button type="button" onclick="openProcessAssignmentModal({{ $process->id }})" class="mt-1 text-xs font-medium text-teal-600 hover:underline">
                    Asignar equipo
                </button>
            @endif
        </td>
        <td class="px-4 py-3">
            <div class="inline-flex items-center gap-1">
                <a href="{{ route('admin.processes.show', $process) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-teal-200 bg-white text-teal-600 hover:bg-teal-50" title="Ver expediente">
                    <i class="fas fa-eye"></i>
                </a>
                @processCanFor($process, 'delete')
                <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este expediente? No se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
                @endprocessCanFor
            </div>
        </td>
    </tr>
@endforeach
@if($processes->isEmpty())
    <tr>
        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No hay expedientes con los filtros aplicados.</td>
    </tr>
@endif
