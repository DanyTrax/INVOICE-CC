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
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Acciones de gestión --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        @if(in_array($quote->status, ['Borrador', 'Enviada']))
            <form action="{{ route('admin.quotes.approve', $quote) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    <i class="fas fa-check-circle mr-2"></i> Aprobar Cotización
                </button>
            </form>
        @endif
        <div class="inline-flex items-center gap-2">
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
        </div>
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
        @if($quote->status !== 'Aprobada')
            <a href="{{ route('admin.quotes.edit', $quote) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded-lg font-medium">
                <i class="fas fa-lock mr-2"></i> Bloqueado por Aprobación
            </span>
        @endif
        @if(!in_array($quote->status, ['Aprobada', 'Anulada']))
            <form action="{{ route('admin.quotes.anular', $quote) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de anular esta cotización?');">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                    <i class="fas fa-times-circle mr-2"></i> Anular
                </button>
            </form>
        @endif
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
                        <th class="px-2 py-2">Tipo de trámite</th>
                        <th class="px-2 py-2">Producto / Descripción</th>
                        @if($quote->show_prev_license_column)
                            <th class="px-2 py-2">Expediente / INVIMA</th>
                        @endif
                        @if($quote->show_raa_column)
                            <th class="px-2 py-2 w-20">RAA</th>
                        @endif
                        <th class="px-2 py-2">Alcance</th>
                        <th class="px-2 py-2 w-28">Valor</th>
                        <th class="px-2 py-2 w-20">Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->quoteItems as $item)
                        <tr class="border-b border-gray-200 {{ $item->is_loan ? 'bg-amber-50' : '' }}">
                            <td class="px-2 py-2">{{ $item->item_position }}</td>
                            <td class="px-2 py-2">{{ $item->serviceType->name ?? '-' }}</td>
                            <td class="px-2 py-2">{{ $item->description ?? '-' }}</td>
                            @if($quote->show_prev_license_column)
                                <td class="px-2 py-2">{{ $item->previous_license ?? '-' }}</td>
                            @endif
                            @if($quote->show_raa_column)
                                <td class="px-2 py-2">{{ $item->raa_code ?? '-' }}</td>
                            @endif
                            <td class="px-2 py-2">{{ Str::limit($item->scope, 40) ?? '-' }}</td>
                            <td class="px-2 py-2">{{ $quote->currency }} {{ number_format($item->fee_value, 2) }}</td>
                            <td class="px-2 py-2">
                                @if($item->is_loan)
                                    <span class="text-amber-700 text-xs">Préstamo</span>
                                @else
                                    <span class="text-gray-500 text-xs">Honorario</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Resumen financiero --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen financiero</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Total honorarios</p>
                <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->total_professional_fees, 2) }}</p>
            </div>
            <div class="bg-amber-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Total suplidos / Préstamos</p>
                <p class="text-xl font-semibold text-gray-900">{{ $quote->currency }} {{ number_format($quote->total_loans, 2) }}</p>
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
                <p class="text-sm text-gray-600 mt-2">Total</p>
                <p class="text-2xl font-bold text-teal-800">{{ $quote->currency }} {{ number_format($quote->total_with_tax, 2) }}</p>
            </div>
        @else
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600">Total</p>
                <p class="text-2xl font-bold text-teal-800">{{ $quote->currency }} {{ number_format($quote->subtotal, 2) }}</p>
            </div>
        @endif
    </div>

    <div class="flex gap-3">
        <a href="{{ route('admin.quotes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
    </div>
@endsection
