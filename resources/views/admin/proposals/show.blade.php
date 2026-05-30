@extends('layouts.admin-flowbite')

@section('title', 'Propuesta ' . $proposal->consecutive . ' - RAMS')

@section('page-title', 'Propuesta ' . $proposal->consecutive)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.proposals.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Propuestas</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">{{ $proposal->consecutive }}</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        @if($proposal->status === \App\Models\Proposal::STATUS_PENDIENTE)
            <form action="{{ route('admin.proposals.approve', $proposal) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    <i class="fas fa-check-circle mr-2"></i> Aprobar propuesta
                </button>
            </form>
        @endif
        <div class="inline-flex items-center gap-2">
            @if(count($proposalPdfTemplates ?? []) > 0)
                <label for="pdf-template-select" class="text-sm text-gray-600">Plantilla PDF:</label>
                <select id="pdf-template-select" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                    @foreach($proposalPdfTemplates as $t)
                        <option value="{{ $t->id }}" {{ $t->is_default ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            @endif
            <a href="{{ route('admin.proposals.pdf', $proposal) }}" id="btn-download-pdf" target="_blank" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-file-pdf mr-2"></i> Descargar PDF
            </a>
        </div>
        @if(count($proposalPdfTemplates ?? []) > 0)
        @push('scripts')
        <script>
        (function() {
            var select = document.getElementById('pdf-template-select');
            var link = document.getElementById('btn-download-pdf');
            if (select && link) {
                function updatePdfLink() {
                    var id = select.value;
                    link.href = '{{ route("admin.proposals.pdf", $proposal) }}' + (id ? '?template_id=' + encodeURIComponent(id) : '');
                }
                select.addEventListener('change', updatePdfLink);
                updatePdfLink();
            }
        })();
        </script>
        @endpush
        @endif
        @if($proposal->status !== \App\Models\Proposal::STATUS_APROBADA)
            <a href="{{ route('admin.proposals.edit', $proposal) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded-lg font-medium">
                <i class="fas fa-lock mr-2"></i> Bloqueado por aprobación
            </span>
        @endif
        <form action="{{ route('admin.proposals.destroy', $proposal) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta propuesta?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 font-medium">
                <i class="fas fa-trash-alt mr-2"></i> Eliminar
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos de la propuesta</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">Cliente</p>
                <p class="font-medium text-gray-900">{{ $proposal->client->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Fecha</p>
                <p class="font-medium text-gray-900">{{ $proposal->date?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Moneda</p>
                <p class="font-medium text-gray-900">{{ $proposal->currency ?? 'COP' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Estado</p>
                @php
                    $statusStyles = [
                        'Pendiente' => 'bg-amber-100 text-amber-900',
                        'Aprobada' => 'bg-green-100 text-green-800',
                    ];
                    $style = $statusStyles[$proposal->status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $proposal->status }}</span>
            </div>
        </div>
        @if($proposal->exchange_rate)
            <p class="mt-3 text-sm text-gray-600">Tasa de cambio: {{ number_format($proposal->exchange_rate, 4) }}</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ítems</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-gray-50 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Concepto</th>
                        <th class="px-4 py-2">Alcance</th>
                        <th class="px-4 py-2 text-right">Honorarios</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proposal->proposalItems as $item)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-2">{{ $item->item_position }}</td>
                            <td class="px-4 py-2 font-medium">{{ $item->concept }}</td>
                            <td class="px-4 py-2 text-gray-700 whitespace-pre-wrap">{{ $item->scope ?: '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $proposal->currency }} {{ number_format($item->fee_value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-sm space-y-1 text-sm border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="flex justify-between"><span>Subtotal</span><span>{{ $proposal->currency }} {{ number_format($proposal->subtotal, 2) }}</span></div>
                @if($proposal->apply_tax && $proposal->tax_percentage !== null)
                    <div class="flex justify-between"><span>IVA ({{ number_format($proposal->tax_percentage, 2) }}%)</span><span>{{ $proposal->currency }} {{ number_format($proposal->tax_amount, 2) }}</span></div>
                @endif
                @if($proposal->apply_bank_fee && $proposal->bank_fee_value !== null)
                    <div class="flex justify-between"><span>Gasto bancario</span><span>{{ $proposal->currency }} {{ number_format($proposal->bank_fee_amount, 2) }}</span></div>
                @endif
                <div class="flex justify-between font-bold text-teal-800 pt-2 border-t border-gray-200"><span>Total</span><span>{{ $proposal->currency }} {{ number_format($proposal->total_with_tax, 2) }}</span></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Texto del PDF</h3>
        <p class="text-sm text-gray-600 mb-3">Edite el membrete, la nota lateral o el pie de cierre. Si un campo queda vacío, al descargar se usa la <strong>plantilla PDF actual</strong>. Guarde aquí después de cambiar el pie para que el PDF refleje su texto nuevo.</p>
        <form action="{{ route('admin.proposals.pdf-footer.update', $proposal) }}" method="POST" class="flex flex-col gap-4">
            @csrf
            @method('PATCH')
            <div>
                <label for="pdf_body_html" class="block mb-1 text-sm font-medium text-gray-700">Contexto / Cuerpo (texto introductorio del PDF)</label>
                <textarea name="pdf_body_html" id="pdf_body_html" rows="12"
                          class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ old('pdf_body_html', $proposal->pdf_body_html ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Desde «Señor(es)…» hasta el párrafo previo a la tabla. La tabla de ítems no se edita aquí.</p>
            </div>
            <div>
                <label for="pdf_side_note_html" class="block mb-1 text-sm font-medium text-gray-700">Nota lateral (junto a totales)</label>
                <textarea name="pdf_side_note_html" id="pdf_side_note_html" rows="4" class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">{{ old('pdf_side_note_html', $proposal->pdf_side_note_html ?? '') }}</textarea>
            </div>
            <div>
                <label for="pdf_footer" class="block mb-1 text-sm font-medium text-gray-700">Pie de página (debajo del total, encima de firma)</label>
                <textarea name="pdf_footer" id="pdf_footer" rows="4" maxlength="2000"
                          placeholder="Vacío = pie de la plantilla PDF actual"
                          class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">{{ old('pdf_footer', $proposal->pdf_footer ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Si ve texto viejo, reemplácelo y pulse «Guardar textos del PDF», o borre el campo para usar solo la plantilla.</p>
            </div>
            <button type="submit" class="self-start px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm">Guardar textos del PDF</button>
        </form>
    </div>

    @include('admin.partials.pdf-body-tinymce-init')
@endsection
