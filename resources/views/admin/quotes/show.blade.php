@extends('layouts.admin-flowbite')

@section('title', 'Cotización ' . $quote->consecutive . ' - RAMS')

@section('page-title', 'Cotización ' . $quote->consecutive)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.quotes.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Cotizaciones</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">{{ $quote->consecutive }}</span>
        </div>
    </li>
@endsection

@section('content')
    {{-- Acciones de gestión --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        @quoteCan('edit')
        @if(in_array($quote->status, ['Borrador', 'Enviada']))
            <form action="{{ route('admin.quotes.approve', $quote) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    <i class="fas fa-check-circle mr-2"></i> Aprobar Cotización
                </button>
            </form>
        @endif
        @endquoteCan
        <div class="inline-flex items-center gap-2 flex-wrap">
            @quoteCan('pdf')
            @if(count($quotePdfTemplates ?? []) > 0)
                <label for="pdf-template-select" class="text-sm text-gray-600">Plantilla PDF:</label>
                <select id="pdf-template-select" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                    @foreach($quotePdfTemplates as $t)
                        <option value="{{ $t->id }}" {{ $t->is_default ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            @endif
            <a href="{{ route('admin.quotes.pdf', $quote) }}" id="btn-download-pdf" target="_blank" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-file-pdf mr-2"></i> Descargar PDF
            </a>
            @if(count($quotePdfTemplates ?? []) > 0)
            @push('scripts')
            <script>
            (function() {
                var select = document.getElementById('pdf-template-select');
                var link = document.getElementById('btn-download-pdf');
                if (select && link) {
                    function updatePdfLink() {
                        var id = select.value;
                        link.href = '{{ route("admin.quotes.pdf", $quote) }}' + (id ? '?template_id=' + encodeURIComponent(id) : '');
                    }
                    select.addEventListener('change', updatePdfLink);
                    updatePdfLink();
                }
            })();
            </script>
            @endpush
            @endif
            @endquoteCan
            <a href="{{ route('admin.processes.monitor', ['quote_id' => $quote->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-folder-open mr-2"></i> Ver solicitudes
            </a>
        </div>
        @if($quote->status !== 'Aprobada')
            @quoteCan('edit')
            <a href="{{ route('admin.quotes.edit', $quote) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
            @endquoteCan
        @else
            <span class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded-lg font-medium">
                <i class="fas fa-lock mr-2"></i> Bloqueado por Aprobación
            </span>
        @endif
        @quoteCan('edit')
        @if(!in_array($quote->status, ['Aprobada', 'Anulada']))
            <button type="button" onclick="document.getElementById('modal-anular-cotizacion').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                <i class="fas fa-times-circle mr-2"></i> Cancelar cotización
            </button>
        @endif
        @endquoteCan
        @quoteCan('delete')
        <form action="{{ route('admin.quotes.destroy', $quote) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta cotización y todos sus ítems? Las solicitudes vinculadas quedarán sin cotización. Esta acción no se puede deshacer.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 font-medium border border-red-800">
                <i class="fas fa-trash-alt mr-2"></i> Eliminar cotización
            </button>
        </form>
        @endquoteCan
    </div>

    {{-- Datos de la cotización --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos de la cotización</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">Cliente</p>
                <p class="font-medium text-gray-900">{{ $quote->client->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Fecha</p>
                <p class="font-medium text-gray-900">{{ $quote->date?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Moneda</p>
                <p class="font-medium text-gray-900">{{ $quote->currency ?? 'COP' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Estado</p>
                @php
                    $statusStyles = [
                        'Borrador' => 'bg-gray-100 text-gray-800',
                        'Enviada' => 'bg-blue-100 text-blue-800',
                        'Aprobada' => 'bg-green-100 text-green-800',
                        'Rechazada' => 'bg-red-100 text-red-800',
                        'Anulada' => 'bg-red-100 text-red-800',
                    ];
                    $style = $statusStyles[$quote->status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $quote->status }}</span>
            </div>
            @if($quote->status === 'Anulada' && $quote->cancellation_note)
                <div class="md:col-span-2 lg:col-span-4">
                    <p class="text-xs text-gray-500 uppercase">Motivo de cancelación</p>
                    <p class="mt-1 text-sm text-gray-800 bg-red-50 border border-red-100 rounded-lg p-3">{{ $quote->cancellation_note }}</p>
                </div>
            @endif
            @if($quote->exchange_rate)
                <div>
                    <p class="text-xs text-gray-500 uppercase">Tasa de cambio</p>
                    <p class="font-medium text-gray-900">{{ number_format($quote->exchange_rate, 4) }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Detalle de ítems --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalle de ítems</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-2 py-2 w-12">#</th>
                        @if($quote->show_row_id_column ?? false)
                            <th class="px-2 py-2 w-24">ROW ID</th>
                        @endif
                        <th class="px-2 py-2">Servicio</th>
                        @if($quote->show_service_type_column)
                            <th class="px-2 py-2">Trámite</th>
                        @endif
                        @if($quote->show_description_column)
                            <th class="px-2 py-2">Producto / Descripción</th>
                        @endif
                        @if($quote->show_prev_license_column)
                            <th class="px-2 py-2">Solicitud / INVIMA</th>
                        @endif
                        @if($quote->show_raa_column)
                            <th class="px-2 py-2 w-20">RAA</th>
                        @endif
                        @if($quote->show_franquicia_column ?? false)
                            <th class="px-2 py-2">Franquicia</th>
                        @endif
                        @if($quote->show_centro_costos_column ?? false)
                            <th class="px-2 py-2">Centro de costos</th>
                        @endif
                        @if($quote->show_contacto_column ?? false)
                            <th class="px-2 py-2">Contacto</th>
                        @endif
                        <th class="px-2 py-2">Alcance</th>
                        <th class="px-2 py-2 w-28">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->quoteItems as $item)
                        <tr class="border-b border-gray-200">
                            <td class="px-2 py-2">{{ $item->item_position }}</td>
                            @if($quote->show_row_id_column ?? false)
                                <td class="px-2 py-2">{{ $item->row_id ?: '–' }}</td>
                            @endif
                            <td class="px-2 py-2">{{ $item->service_label ?: ($item->service?->name ?? '-') }}</td>
                            @if($quote->show_service_type_column)
                                <td class="px-2 py-2">
                                    @php
                                        $linkedCycle = $item->submissions?->sortByDesc('id')->first();
                                        $linkedProcess = $linkedCycle?->process;
                                        // Ítem (tras vincular ciclo) o solicitud vinculada al ítem
                                        $tramiteNombre = $item->serviceType?->name
                                            ?? $linkedProcess?->serviceType?->name
                                            ?? $item->process?->serviceType?->name;
                                    @endphp
                                    @if($linkedProcess)
                                        <a href="{{ route('admin.processes.show', $linkedProcess) }}" class="text-teal-600 hover:text-teal-800 hover:underline">
                                            {{ $tramiteNombre ?: 'Solicitud' }}
                                        </a>
                                    @else
                                        {{ $tramiteNombre ?: '–' }}
                                    @endif
                                </td>
                            @endif
                            @if($quote->show_description_column)
                                <td class="px-2 py-2">{{ $item->description ?? '-' }}</td>
                            @endif
                            @if($quote->show_prev_license_column)
                                <td class="px-2 py-2">{{ $item->previous_license ?? '-' }}</td>
                            @endif
                            @if($quote->show_raa_column)
                                <td class="px-2 py-2">{{ $item->raa_code ?? '-' }}</td>
                            @endif
                            @if($quote->show_franquicia_column ?? false)
                                <td class="px-2 py-2">{{ $item->franquicia ?: '–' }}</td>
                            @endif
                            @if($quote->show_centro_costos_column ?? false)
                                <td class="px-2 py-2">{{ $item->centro_costos ?: '–' }}</td>
                            @endif
                            @if($quote->show_contacto_column ?? false)
                                <td class="px-2 py-2">{{ $item->contacto ?: '–' }}</td>
                            @endif
                            <td class="px-2 py-2">{{ Str::limit($item->scope, 40) ?? '-' }}</td>
                            <td class="px-2 py-2">{{ $quote->currency }} {{ number_format($item->fee_value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Resumen financiero --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen financiero</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Total honorarios</p>
                <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->total_professional_fees, 2) }}</p>
            </div>
            <div class="bg-teal-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Tasas INVIMA</p>
                <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->total_invima_fees, 2) }}</p>
            </div>
        </div>
        @if($quote->apply_tax && $quote->tax_percentage !== null)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600">Sub-total</p>
                <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->subtotal, 2) }}</p>
                <p class="text-sm text-gray-600 mt-2">IVA ({{ number_format($quote->tax_percentage, 2) }}%)</p>
                <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->tax_amount, 2) }}</p>
                @if($quote->apply_bank_fee && $quote->bank_fee_value !== null)
                    <p class="text-sm text-gray-600 mt-2">Gasto bancario</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->bank_fee_amount, 2) }}</p>
                @endif
                <p class="text-sm text-gray-600 mt-2">Total</p>
                <p class="text-2xl font-bold text-teal-800">{{ $quote->currency }} {{ number_format($quote->total_with_tax, 2) }}</p>
            </div>
        @else
            <div class="mt-4 pt-4 border-t border-gray-200">
                @if($quote->apply_bank_fee && $quote->bank_fee_value !== null)
                    <p class="text-sm text-gray-600">Subtotal</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->subtotal, 2) }}</p>
                    <p class="text-sm text-gray-600 mt-2">Gasto bancario</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->bank_fee_amount, 2) }}</p>
                @endif
                <p class="text-sm text-gray-600 mt-2">Total</p>
                <p class="text-2xl font-bold text-teal-800">{{ $quote->currency }} {{ number_format($quote->total_with_tax, 2) }}</p>
            </div>
        @endif
    </div>

    {{-- Pie de página del PDF (editable) --}}
    @quoteCan('edit')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Pie de página del PDF</h3>
        <p class="text-sm text-gray-600 mb-3">Este texto se muestra en la parte inferior de la cotización al descargar el PDF. Si está vacío, se usará el pie definido en la plantilla o en Configuración.</p>
        <form action="{{ route('admin.quotes.pdf-footer.update', $quote) }}" method="POST" class="flex flex-col gap-3">
            @csrf
            @method('PATCH')
            <textarea name="pdf_footer" rows="3" maxlength="1000" placeholder="Ej: RAMS - Regulatory Affairs Management System"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ old('pdf_footer', $quote->pdf_footer ?? '') }}</textarea>
            @error('pdf_footer')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500">Máximo 1000 caracteres.</p>
            <button type="submit" class="self-start inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium text-sm">
                <i class="fas fa-save mr-2"></i> Guardar pie de página
            </button>
        </form>
    </div>
    @endquoteCan

    <div class="flex gap-3">
        <a href="{{ route('admin.quotes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
    </div>

    {{-- Modal: Cancelar cotización con motivo --}}
    @quoteCan('edit')
    <div id="modal-anular-cotizacion" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('modal-anular-cotizacion').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Cancelar cotización</h4>
                <p class="text-sm text-gray-600 mb-4">Indique el motivo por el cual se cancela esta cotización. Este texto quedará registrado.</p>
                <form action="{{ route('admin.quotes.anular', $quote) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="cancellation_note" class="block text-sm font-medium text-gray-700 mb-1">Motivo de cancelación <span class="text-red-500">*</span></label>
                        <textarea id="cancellation_note" name="cancellation_note" rows="4" required maxlength="2000"
                                  class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500"
                                  placeholder="Ej: Cliente desistió del trámite / Error en datos / Duplicado..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">Máximo 2000 caracteres.</p>
                        @error('cancellation_note')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-anular-cotizacion').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cerrar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                            <i class="fas fa-times-circle mr-1"></i> Confirmar cancelación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endquoteCan
@endsection
