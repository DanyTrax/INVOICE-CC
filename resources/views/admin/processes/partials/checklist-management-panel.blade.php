@php
    use App\Models\ChecklistItem;

    $items = $items ?? collect();
    $accent = $accent ?? 'teal';
    $isAmber = $accent === 'amber';
    $borderClass = $isAmber ? 'border-amber-200' : 'border-gray-200';
    $iconClass = $isAmber ? 'text-amber-600' : 'text-teal-600';
    $btnClass = $isAmber ? 'bg-amber-600 hover:bg-amber-700' : 'bg-teal-600 hover:bg-teal-700';
    $statusOrder = [
        ChecklistItem::STATUS_PENDIENTE,
        ChecklistItem::STATUS_RECIBIDO,
        ChecklistItem::STATUS_TRADUCCION,
        ChecklistItem::STATUS_APROBADO,
    ];
    $statusBadgeMap = [
        ChecklistItem::STATUS_PENDIENTE => 'bg-gray-100 text-gray-700',
        ChecklistItem::STATUS_RECIBIDO => 'bg-blue-100 text-blue-800',
        ChecklistItem::STATUS_TRADUCCION => 'bg-yellow-100 text-yellow-800',
        ChecklistItem::STATUS_APROBADO => 'bg-green-100 text-green-800',
    ];
    $statusCounts = $items->countBy('status');
    $totalCount = $items->count();
@endphp

<div class="bg-white rounded-lg shadow-sm border {{ $borderClass }} p-4 sm:p-6 mb-6" x-data="{ open: false }">
    <div class="flex items-start sm:items-center justify-between gap-3" :class="open ? 'mb-4' : ''">
        <button type="button"
                @click="open = !open"
                class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-1 sm:gap-2 min-w-0 flex-1 text-left rounded-lg -ml-1 px-1 py-1 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500/40"
                :aria-expanded="open">
            <span class="inline-flex items-center gap-2 min-w-0">
                <i class="fas fa-chevron-right w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200"
                   :class="{ 'rotate-90': open }"></i>
                <span class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-folder-open {{ $iconClass }} mr-2"></i>{{ $title }}
                </span>
            </span>
            <span x-show="!open" x-cloak class="flex flex-wrap items-center gap-1 pl-6 sm:pl-0 text-[11px] leading-tight">
                @if($totalCount === 0)
                    <span class="text-gray-400 font-normal">Sin documentos</span>
                @else
                    <span class="text-gray-500">{{ $totalCount }} {{ $totalCount === 1 ? 'doc.' : 'docs.' }}</span>
                    @foreach($statusOrder as $status)
                        @php $count = (int) ($statusCounts[$status] ?? 0); @endphp
                        @if($count > 0)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $statusBadgeMap[$status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $count }} {{ $status }}
                            </span>
                        @endif
                    @endforeach
                    @foreach($statusCounts as $status => $count)
                        @if(! in_array($status, $statusOrder, true) && $count > 0)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">
                                {{ $count }} {{ $status }}
                            </span>
                        @endif
                    @endforeach
                @endif
            </span>
        </button>
        @processCanFor($process, 'feed')
        <button type="button"
                onclick="openAddDocumentModal({{ (int) $addModalFlag }})"
                class="inline-flex items-center shrink-0 px-3 py-2 {{ $btnClass }} text-white text-sm font-medium rounded-lg">
            <i class="fas fa-plus mr-2"></i> {{ $addButtonLabel }}
        </button>
        @endprocessCanFor
    </div>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-3 py-2">Documento</th>
                        <th class="px-3 py-2 w-32">Estado</th>
                        <th class="px-3 py-2">Observación</th>
                        <th class="px-3 py-2 w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $badgeClass = match($item->status) {
                                'Aprobado' => 'bg-green-100 text-green-800',
                                'Recibido' => 'bg-blue-100 text-blue-800',
                                'Traducción' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            @include('admin.processes.partials.checklist-item-table-document', ['item' => $item])
                            <td class="px-3 py-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeClass }}">{{ $item->status }}</span>
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ Str::limit($item->observation_agent ?? '-', 50) }}</td>
                            <td class="px-3 py-2">
                                @processCanFor($process, 'edit')
                                <button type="button" onclick="openChecklistModal({{ $item->id }}, '{{ addslashes($item->document_name) }}', '{{ $item->status }}', '{{ addslashes($item->observation_agent ?? '') }}')"
                                        class="text-teal-600 hover:text-teal-800 text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i> Cambiar estado
                                </button>
                                @endprocessCanFor
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">{{ $emptyMessage }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
