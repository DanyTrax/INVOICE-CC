@extends('layouts.admin-flowbite')

@section('title', 'Editar Cotización ' . $quote->consecutive . ' - RAMS')

@section('page-title', 'Editar Cotización')

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
            <a href="{{ route('admin.quotes.show', $quote) }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">{{ $quote->consecutive }}</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Editar</span>
        </div>
    </li>
@endsection

@section('content')
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <p class="font-medium mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Corrija los errores antes de enviar.</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.quotes.update', $quote) }}" method="POST" id="form-quote">
        @csrf
        @method('PUT')
        <input type="hidden" name="show_prev_license_column" id="input-show-prev-license" value="{{ old('show_prev_license_column', $quote->show_prev_license_column) ? '1' : '0' }}">
        <input type="hidden" name="show_raa_column" id="input-show-raa" value="{{ old('show_raa_column', $quote->show_raa_column) ? '1' : '0' }}">
        <input type="hidden" name="show_service_type_column" id="input-show-tramite" value="{{ old('show_service_type_column', $quote->show_service_type_column) ? '1' : '0' }}">
        <input type="hidden" name="show_description_column" id="input-show-description" value="{{ old('show_description_column', $quote->show_description_column ?? true) ? '1' : '0' }}">
        <input type="hidden" name="show_row_id_column" id="input-show-row-id" value="{{ old('show_row_id_column', $quote->show_row_id_column ?? false) ? '1' : '0' }}">
        <input type="hidden" name="show_franquicia_column" id="input-show-franquicia" value="{{ old('show_franquicia_column', $quote->show_franquicia_column ?? false) ? '1' : '0' }}">
        <input type="hidden" name="show_centro_costos_column" id="input-show-centro-costos" value="{{ old('show_centro_costos_column', $quote->show_centro_costos_column ?? false) ? '1' : '0' }}">
        <input type="hidden" name="show_contacto_column" id="input-show-contacto" value="{{ old('show_contacto_column', $quote->show_contacto_column ?? false) ? '1' : '0' }}">

        {{-- Cabecera --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos de la cotización</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="client_id" class="block mb-2 text-sm font-medium text-gray-900">Cliente <span class="text-red-500">*</span></label>
                    <select name="client_id" id="client_id" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="">Seleccione...</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('client_id', $quote->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" class="block mb-2 text-sm font-medium text-gray-900">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', $quote->date?->format('Y-m-d')) }}" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="currency" class="block mb-2 text-sm font-medium text-gray-900">Moneda de la Oferta <span class="text-red-500">*</span></label>
                    <select name="currency" id="currency" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="COP" {{ old('currency', $quote->currency) === 'COP' ? 'selected' : '' }}>COP</option>
                        <option value="USD" {{ old('currency', $quote->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
                <div>
                    <label for="consecutive" class="block mb-2 text-sm font-medium text-gray-900">COTIZACIÓN No. <span class="text-red-500">*</span></label>
                    <input type="text" name="consecutive" id="consecutive"
                           value="{{ old('consecutive', $quote->consecutive) }}"
                           placeholder="Ej: 001-26" required maxlength="32"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
                <div class="md:col-span-2">
                    <label for="exchange_rate" class="block mb-2 text-sm font-medium text-gray-900">Tasa de cambio (si aplica)</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" value="{{ old('exchange_rate', $quote->exchange_rate) }}" min="0" step="0.000001"
                           placeholder="Ej: 4000.50 para ofertas en USD"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                </div>
            </div>
        </div>

        {{-- Detalle de ítems --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detalle de ítems</h3>
                <div class="flex gap-2">
                    <button type="button" id="btn-add-row" class="inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700">
                        <i class="fas fa-plus mr-2"></i> Agregar fila
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 mb-3 text-sm text-gray-700">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-prev-license" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_prev_license_column', $quote->show_prev_license_column) ? 'checked' : '' }}>
                    <span>Usar columna Solicitud / INVIMA</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-raa" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_raa_column', $quote->show_raa_column) ? 'checked' : '' }}>
                    <span>Usar columna RAA</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-tramite" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_service_type_column', $quote->show_service_type_column) ? 'checked' : '' }}>
                    <span>Usar columna Trámite</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-description" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_description_column', $quote->show_description_column ?? true) ? 'checked' : '' }}>
                    <span>Usar columna Producto / Descripción</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-row-id" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_row_id_column', $quote->show_row_id_column ?? false) ? 'checked' : '' }}>
                    <span>Usar columna ROW ID</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-franquicia" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_franquicia_column', $quote->show_franquicia_column ?? false) ? 'checked' : '' }}>
                    <span>Usar columna Franquicia</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-centro-costos" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_centro_costos_column', $quote->show_centro_costos_column ?? false) ? 'checked' : '' }}>
                    <span>Usar columna Centro de costos</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="toggle-contacto" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('show_contacto_column', $quote->show_contacto_column ?? false) ? 'checked' : '' }}>
                    <span>Usar columna Contacto</span>
                </label>
                <label class="inline-flex items-center gap-2 ml-4">
                    <input type="checkbox" name="apply_tax" id="toggle-apply-tax" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('apply_tax', $quote->apply_tax) ? 'checked' : '' }}>
                    <span>Aplicar impuesto (IVA)</span>
                </label>
                <span id="tax-pct-wrap" class="{{ old('apply_tax', $quote->apply_tax) ? '' : 'hidden' }}">
                    <label for="tax_percentage" class="sr-only">Porcentaje IVA</label>
                    <input type="number" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage', $quote->tax_percentage ?? '19') }}" min="0" max="100" step="0.01" placeholder="%"
                           class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm"> %
                </span>
                <label class="inline-flex items-center gap-2 ml-4">
                    <input type="checkbox" name="apply_bank_fee" id="toggle-bank-fee" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('apply_bank_fee', $quote->apply_bank_fee) ? 'checked' : '' }}>
                    <span>Gasto bancario</span>
                </label>
                <span id="bank-fee-wrap" class="{{ old('apply_bank_fee', $quote->apply_bank_fee) ? '' : 'hidden' }}">
                    <label for="bank_fee_value" class="sr-only">Valor gasto bancario</label>
                    <input type="number" name="bank_fee_value" id="bank_fee_value" value="{{ old('bank_fee_value', $quote->bank_fee_value ?? '0') }}" min="0" step="0.01" placeholder="Valor"
                           class="w-28 border border-gray-300 rounded-lg px-2 py-1 text-sm">
                </span>
            </div>
            <div class="overflow-x-auto">
                @php
                    $hasLinkedSolicitud = $quote->quoteItems->contains(
                        fn ($qi) => $qi->resolveLinkedSolicitudButton() !== null
                    );
                @endphp
                <table class="w-full text-sm text-left text-gray-700 min-w-[1100px]" id="items-table">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-2 py-2 w-12">#</th>
                            <th class="px-2 py-2 w-24" data-col="row-id">ROW ID</th>
                            <th class="px-2 py-2">Servicio</th>
                            <th class="px-2 py-2" data-col="tramite">Trámite (opcional)</th>
                            <th class="px-2 py-2" data-col="description">Producto / Descripción</th>
                            <th class="px-2 py-2" data-col="prev-license">Solicitud / INVIMA</th>
                            <th class="px-2 py-2 w-20" data-col="raa">RAA</th>
                            <th class="px-2 py-2" data-col="franquicia">Franquicia</th>
                            <th class="px-2 py-2" data-col="centro-costos">Centro de costos</th>
                            <th class="px-2 py-2" data-col="contacto">Contacto</th>
                            <th class="px-2 py-2">Alcance</th>
                            <th class="px-2 py-2 w-28">Valor</th>
                            @if($hasLinkedSolicitud)
                                <th class="px-2 py-2 w-36">S.Vinculada</th>
                            @endif
                            <th class="px-2 py-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody">
                        @php
                            $oldItems = old('items', []);
                            if (empty($oldItems)) {
                                $oldItems = $quote->quoteItems->sortBy('item_position')->values()->map(function ($qi) {
                                    return [
                                        'id' => $qi->id,
                                        'item_position' => $qi->item_position,
                                        'service_id' => $qi->service_id ?? '',
                                        'service_label' => $qi->service_label ?? ($qi->service?->name ?? ''),
                                        'service_type_name' => $qi->serviceType->name ?? '',
                                        'description' => $qi->description ?? '',
                                        'row_id' => $qi->row_id ?? '',
                                        'previous_license' => $qi->previous_license ?? '',
                                        'raa_code' => $qi->raa_code ?? '',
                                        'franquicia' => $qi->franquicia ?? '',
                                        'centro_costos' => $qi->centro_costos ?? '',
                                        'contacto' => $qi->contacto ?? '',
                                        'scope' => $qi->scope ?? '',
                                        'fee_value' => $qi->fee_value,
                                        'invima_rate_code' => $qi->invima_rate_code ?? '',
                                        'invima_rate_value' => $qi->invima_rate_value ?? 0,
                                    ];
                                })->toArray();
                            }
                            if (empty($oldItems)) {
                                $oldItems = [['id' => '', 'item_position' => 1, 'service_id' => '', 'service_type_name' => '', 'description' => '', 'row_id' => '', 'previous_license' => '', 'raa_code' => '', 'franquicia' => '', 'centro_costos' => '', 'contacto' => '', 'scope' => '', 'fee_value' => '', 'invima_rate_code' => '', 'invima_rate_value' => '']];
                            }
                        @endphp
                        @foreach($oldItems as $idx => $item)
                            <tr class="item-row border-b border-gray-200">
                                <td class="px-2 py-2 item-num">{{ $idx + 1 }}</td>
                                <td class="px-2 py-2" data-col="row-id">
                                    <input type="text" name="items[{{ $idx }}][row_id]" value="{{ $item['row_id'] ?? '' }}" placeholder="ROW ID" maxlength="128"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2">
                                    @php $selectedService = $services->firstWhere('id', $item['service_id'] ?? null); @endphp
                                    <input type="text" value="{{ ($item['service_label'] ?? '') !== '' ? $item['service_label'] : ($selectedService ? $selectedService->name : '') }}" placeholder="Escriba y elija de la lista (obligatorio)" autocomplete="one-time-code" spellcheck="false"
                                           class="item-service-input border border-gray-300 rounded-lg p-2 w-full text-sm bg-white">
                                    <input type="hidden" name="items[{{ $idx }}][service_id]" class="item-service-id-input" value="{{ $item['service_id'] ?? '' }}">
                                    <input type="hidden" name="items[{{ $idx }}][service_label]" class="item-service-label-input" value="{{ $item['service_label'] ?? ($selectedService ? $selectedService->name : '') }}">
                                </td>
                                <td class="px-2 py-2" data-col="tramite">
                                    <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item['id'] ?? '' }}">
                                    <input type="hidden" name="items[{{ $idx }}][item_position]" value="{{ $idx + 1 }}">
                                    <textarea
                                        name="items[{{ $idx }}][service_type_name]"
                                        list="service_types_datalist"
                                        rows="2"
                                        placeholder="Trámite (opcional)"
                                        class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm resize-y">{{ $item['service_type_name'] ?? '' }}</textarea>
                                </td>
                                <td class="px-2 py-2" data-col="description">
                                    <input type="text" name="items[{{ $idx }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="Producto / Descripción" maxlength="500"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm item-description-input">
                                </td>
                                <td class="px-2 py-2" data-col="prev-license">
                                    <input type="text" name="items[{{ $idx }}][previous_license]" value="{{ $item['previous_license'] ?? '' }}" placeholder="Ej: 2021DM-0006049" maxlength="64"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2" data-col="raa">
                                    <input type="text" name="items[{{ $idx }}][raa_code]" value="{{ $item['raa_code'] ?? '' }}" placeholder="Ej: 141153" maxlength="64"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2" data-col="franquicia">
                                    <input type="text" name="items[{{ $idx }}][franquicia]" value="{{ $item['franquicia'] ?? '' }}" placeholder="Franquicia" maxlength="255"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2" data-col="centro-costos">
                                    <input type="text" name="items[{{ $idx }}][centro_costos]" value="{{ $item['centro_costos'] ?? '' }}" placeholder="Centro de costos" maxlength="255"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2" data-col="contacto">
                                    <input type="text" name="items[{{ $idx }}][contacto]" value="{{ $item['contacto'] ?? '' }}" placeholder="Contacto" maxlength="255"
                                           class="border border-gray-300 rounded-lg p-2 w-full text-sm">
                                </td>
                                <td class="px-2 py-2">
                                    <textarea name="items[{{ $idx }}][scope]" rows="2" placeholder="Alcance" maxlength="1000"
                                              class="js-autoresize item-scope-input border border-gray-300 rounded-lg p-2 w-full text-sm resize-y">{{ $item['scope'] ?? '' }}</textarea>
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" name="items[{{ $idx }}][fee_value]" value="{{ $item['fee_value'] ?? '' }}" placeholder="0" min="0" step="0.01" required
                                           class="item-value border border-gray-300 rounded-lg p-2 w-full text-sm">
                                    <input type="hidden" name="items[{{ $idx }}][invima_rate_code]" value="{{ $item['invima_rate_code'] ?? '' }}">
                                    <input type="hidden" name="items[{{ $idx }}][invima_rate_value]" value="{{ $item['invima_rate_value'] ?? '0' }}">
                                </td>
                                @if($hasLinkedSolicitud)
                                    <td class="px-2 py-2">
                                        @php
                                            $quoteItemForLink = ! empty($item['id'])
                                                ? $quote->quoteItems->firstWhere('id', (int) $item['id'])
                                                : null;
                                        @endphp
                                        @if($quoteItemForLink)
                                            @include('admin.quotes.partials.item-linked-solicitud-button', ['item' => $quoteItemForLink])
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-2 py-2">
                                    <button type="button" class="btn-remove-row text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-trash-alt"></i></button>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total honorarios</p>
                    <p class="text-xl font-semibold text-gray-900" id="display-total-fees">0</p>
                </div>
                <div class="bg-teal-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Tasas INVIMA</p>
                    <p class="text-xl font-semibold text-gray-900" id="display-total-invima">0</p>
                </div>
            </div>
            <div id="resumen-sin-iva" class="mt-4 pt-4 border-t border-gray-200 {{ old('apply_tax', $quote->apply_tax) ? 'hidden' : '' }}">
                <p id="sin-iva-subtotal-label" class="text-sm text-gray-600 hidden">Subtotal</p>
                <p id="sin-iva-subtotal-value" class="text-xl font-semibold text-gray-900 hidden">0</p>
                <p id="sin-iva-bank-fee-wrap" class="text-sm text-gray-600 mt-2 hidden">Gasto bancario</p>
                <p id="sin-iva-bank-fee-value" class="text-xl font-semibold text-gray-900 hidden">0</p>
                <p class="text-sm text-gray-600 mt-2">Total</p>
                <p class="text-2xl font-bold text-teal-800" id="display-grand-total">0</p>
            </div>
            <div id="resumen-con-iva" class="mt-4 pt-4 border-t border-gray-200 {{ old('apply_tax', $quote->apply_tax) ? '' : 'hidden' }}">
                <p class="text-sm text-gray-600">Sub-total</p>
                <p class="text-xl font-semibold text-gray-900" id="display-subtotal">0</p>
                <p class="text-sm text-gray-600 mt-2">IVA (<span id="display-iva-pct">0</span>%)</p>
                <p class="text-xl font-semibold text-gray-900" id="display-iva-amount">0</p>
                <p id="con-iva-bank-fee-wrap" class="text-sm text-gray-600 mt-2 hidden">Gasto bancario</p>
                <p id="con-iva-bank-fee-value" class="text-xl font-semibold text-gray-900 hidden">0</p>
                <p class="text-sm text-gray-600 mt-2">Total</p>
                <p class="text-2xl font-bold text-teal-800" id="display-total-with-tax">0</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-save mr-2"></i> Actualizar cotización
            </button>
            <a href="{{ route('admin.quotes.show', $quote) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                Cancelar
            </a>
        </div>
    </form>

    <datalist id="service_types_datalist">
        @foreach($serviceTypes as $st)
            <option value="{{ $st->name }}"></option>
        @endforeach
    </datalist>
    <datalist id="services_datalist">
        @foreach($services as $s)
            <option value="{{ $s->name }}"></option>
        @endforeach
    </datalist>

    <template id="row-template-normal">
        <tr class="item-row border-b border-gray-200">
            <td class="px-2 py-2 item-num"></td>
            <td class="px-2 py-2" data-col="row-id">
                <input type="text" name="items[__INDEX__][row_id]" placeholder="ROW ID" maxlength="128"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2">
                <input type="text" placeholder="Escriba y elija de la lista (obligatorio)" autocomplete="one-time-code" spellcheck="false"
                       class="item-service-input border border-gray-300 rounded-lg p-2 w-full text-sm bg-white">
                <input type="hidden" name="items[__INDEX__][service_id]" class="item-service-id-input" value="">
                <input type="hidden" name="items[__INDEX__][service_label]" class="item-service-label-input" value="">
            </td>
            <td class="px-2 py-2" data-col="tramite">
                <input type="hidden" name="items[__INDEX__][id]" value="">
                <input type="hidden" name="items[__INDEX__][item_position]" value="0" class="item-position-input">
                <textarea name="items[__INDEX__][service_type_name]" list="service_types_datalist" rows="2" placeholder="Trámite (opcional)"
                          class="js-autoresize border border-gray-300 rounded-lg p-2 w-full text-sm resize-y"></textarea>
            </td>
            <td class="px-2 py-2" data-col="description">
                <input type="text" name="items[__INDEX__][description]" placeholder="Producto / Descripción" maxlength="500"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm item-description-input">
            </td>
            <td class="px-2 py-2" data-col="prev-license">
                <input type="text" name="items[__INDEX__][previous_license]" placeholder="Ej: 2021DM-0006049" maxlength="64"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2" data-col="raa">
                <input type="text" name="items[__INDEX__][raa_code]" placeholder="Ej: 141153" maxlength="64"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2" data-col="franquicia">
                <input type="text" name="items[__INDEX__][franquicia]" placeholder="Franquicia" maxlength="255"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2" data-col="centro-costos">
                <input type="text" name="items[__INDEX__][centro_costos]" placeholder="Centro de costos" maxlength="255"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2" data-col="contacto">
                <input type="text" name="items[__INDEX__][contacto]" placeholder="Contacto" maxlength="255"
                       class="border border-gray-300 rounded-lg p-2 w-full text-sm">
            </td>
            <td class="px-2 py-2">
                <textarea name="items[__INDEX__][scope]" rows="2" placeholder="Alcance" maxlength="1000"
                          class="js-autoresize item-scope-input border border-gray-300 rounded-lg p-2 w-full text-sm resize-y"></textarea>
            </td>
            <td class="px-2 py-2">
                <input type="number" name="items[__INDEX__][fee_value]" placeholder="0" min="0" step="0.01" required
                       class="item-value border border-gray-300 rounded-lg p-2 w-full text-sm">
                <input type="hidden" name="items[__INDEX__][invima_rate_code]" value="">
                <input type="hidden" name="items[__INDEX__][invima_rate_value]" value="0">
            </td>
            @if($hasLinkedSolicitud)
            <td class="px-2 py-2"><span class="text-gray-400 text-sm">—</span></td>
            @endif
            <td class="px-2 py-2">
                <button type="button" class="btn-remove-row text-red-600 hover:text-red-800 p-1" title="Quitar fila"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>

    @push('scripts')
    <script>
    (function() {
        const tbody = document.getElementById('items-tbody');
        const templateNormal = document.getElementById('row-template-normal');
        const btnAdd = document.getElementById('btn-add-row');
        const togglePrev = document.getElementById('toggle-prev-license');
        const toggleRaa = document.getElementById('toggle-raa');
        const toggleTramite = document.getElementById('toggle-tramite');
        const toggleDescription = document.getElementById('toggle-description');
        const toggleRowId = document.getElementById('toggle-row-id');
        const toggleFranquicia = document.getElementById('toggle-franquicia');
        const toggleCentroCostos = document.getElementById('toggle-centro-costos');
        const toggleContacto = document.getElementById('toggle-contacto');
        const inputShowTramite = document.getElementById('input-show-tramite');
        const toggleApplyTax = document.getElementById('toggle-apply-tax');
        const taxPctWrap = document.getElementById('tax-pct-wrap');
        const inputShowPrevLicense = document.getElementById('input-show-prev-license');
        const inputShowRaa = document.getElementById('input-show-raa');
        const inputShowDescription = document.getElementById('input-show-description');
        const inputShowRowId = document.getElementById('input-show-row-id');
        const inputShowFranquicia = document.getElementById('input-show-franquicia');
        const inputShowCentroCostos = document.getElementById('input-show-centro-costos');
        const inputShowContacto = document.getElementById('input-show-contacto');
        let rowIndex = {{ count($oldItems) }};

        function syncColumnHiddenInputs() {
            if (inputShowPrevLicense) inputShowPrevLicense.value = togglePrev?.checked ? '1' : '0';
            if (inputShowRaa) inputShowRaa.value = toggleRaa?.checked ? '1' : '0';
            if (inputShowTramite && toggleTramite) inputShowTramite.value = toggleTramite.checked ? '1' : '0';
            if (inputShowDescription && toggleDescription) inputShowDescription.value = toggleDescription.checked ? '1' : '0';
            if (inputShowRowId && toggleRowId) inputShowRowId.value = toggleRowId.checked ? '1' : '0';
            if (inputShowFranquicia && toggleFranquicia) inputShowFranquicia.value = toggleFranquicia.checked ? '1' : '0';
            if (inputShowCentroCostos && toggleCentroCostos) inputShowCentroCostos.value = toggleCentroCostos.checked ? '1' : '0';
            if (inputShowContacto && toggleContacto) inputShowContacto.value = toggleContacto.checked ? '1' : '0';
        }
        function updateTaxSectionVisibility() {
            const applyTax = toggleApplyTax?.checked;
            if (taxPctWrap) taxPctWrap.classList.toggle('hidden', !applyTax);
            const sinIva = document.getElementById('resumen-sin-iva');
            const conIva = document.getElementById('resumen-con-iva');
            if (sinIva) sinIva.classList.toggle('hidden', !!applyTax);
            if (conIva) conIva.classList.toggle('hidden', !applyTax);
            updateTotals();
        }
        function updateBankFeeVisibility() {
            const wrap = document.getElementById('bank-fee-wrap');
            const applyBankFee = document.getElementById('toggle-bank-fee')?.checked;
            if (wrap) wrap.classList.toggle('hidden', !applyBankFee);
            updateTotals();
        }

        function setColumnEnabled(key, enabled) {
            document.querySelectorAll('[data-col="' + key + '"]').forEach(function(cell) {
                cell.classList.toggle('hidden', !enabled);
                cell.querySelectorAll('input,textarea,select').forEach(function(el) { el.disabled = !enabled; });
            });
        }
        function updateColumnVisibility() {
            if (togglePrev) setColumnEnabled('prev-license', togglePrev.checked);
            if (toggleRaa) setColumnEnabled('raa', toggleRaa.checked);
            if (toggleTramite) setColumnEnabled('tramite', toggleTramite.checked);
            if (toggleDescription) setColumnEnabled('description', toggleDescription.checked);
            if (toggleRowId) setColumnEnabled('row-id', toggleRowId.checked);
            if (toggleFranquicia) setColumnEnabled('franquicia', toggleFranquicia.checked);
            if (toggleCentroCostos) setColumnEnabled('centro-costos', toggleCentroCostos.checked);
            if (toggleContacto) setColumnEnabled('contacto', toggleContacto.checked);
            syncColumnHiddenInputs();
        }
        function addRow() {
            const html = templateNormal.innerHTML.replace(/__INDEX__/g, rowIndex);
            tbody.insertAdjacentHTML('beforeend', html);
            const row = tbody.lastElementChild;
            const posInput = row.querySelector('.item-position-input');
            if (posInput) posInput.value = rowIndex + 1;
            rowIndex++;
            tbody.querySelectorAll('.item-row').forEach(function(r, i) {
                const n = r.querySelector('.item-num');
                if (n) n.textContent = i + 1;
            });
            const removeBtn = row.querySelector('.btn-remove-row');
            if (removeBtn) removeBtn.addEventListener('click', function() {
                if (tbody.querySelectorAll('.item-row').length <= 1) return;
                row.remove();
                rowIndex = 0;
                tbody.querySelectorAll('.item-row').forEach(function(r) {
                    r.querySelectorAll('[name^="items["]').forEach(function(el) {
                        el.name = el.name.replace(/items\[\d+\]/, 'items[' + rowIndex + ']');
                    });
                    const p = r.querySelector('.item-position-input');
                    if (p) p.value = rowIndex + 1;
                    const num = r.querySelector('.item-num');
                    if (num) num.textContent = rowIndex + 1;
                    rowIndex++;
                });
                updateTotals();
            });
            row.querySelector('.item-value').addEventListener('input', updateTotals);
            row.querySelectorAll('textarea.js-autoresize').forEach(function(ta) {
                ta.style.height = 'auto';
                ta.style.height = (ta.scrollHeight || 0) + 'px';
                ta.addEventListener('input', function() { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 'px'; });
            });
            updateColumnVisibility();
            updateTotals();
        }
        function updateTotals() {
            let totalFees = 0, totalInvima = 0;
            tbody.querySelectorAll('.item-row').forEach(function(row) {
                const val = parseFloat(row.querySelector('.item-value')?.value || 0) || 0;
                totalFees += val;
                const invimaInput = row.querySelector('input[name$="[invima_rate_value]"]');
                if (invimaInput) totalInvima += parseFloat(invimaInput.value || 0) || 0;
            });
            const subtotal = totalFees + totalInvima;
            const applyTax = toggleApplyTax?.checked;
            const taxPct = parseFloat(document.getElementById('tax_percentage')?.value || 0) || 0;
            const ivaAmount = applyTax ? Math.round(subtotal * taxPct / 100 * 100) / 100 : 0;
            const applyBankFee = document.getElementById('toggle-bank-fee')?.checked;
            const bankFee = applyBankFee ? (parseFloat(document.getElementById('bank_fee_value')?.value || 0) || 0) : 0;
            const totalWithTax = subtotal + ivaAmount + bankFee;
            const fmt = function(n) { return new Intl.NumberFormat('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n); };
            const feesEl = document.getElementById('display-total-fees');
            const invimaEl = document.getElementById('display-total-invima');
            if (feesEl) feesEl.textContent = fmt(totalFees);
            if (invimaEl) invimaEl.textContent = fmt(totalInvima);
            const grandEl = document.getElementById('display-grand-total');
            const subtotalEl = document.getElementById('display-subtotal');
            const ivaPctEl = document.getElementById('display-iva-pct');
            const ivaAmountEl = document.getElementById('display-iva-amount');
            const totalWithTaxEl = document.getElementById('display-total-with-tax');
            const sinIvaSubtotalLabel = document.getElementById('sin-iva-subtotal-label');
            const sinIvaSubtotalValue = document.getElementById('sin-iva-subtotal-value');
            const sinIvaBankFeeWrap = document.getElementById('sin-iva-bank-fee-wrap');
            const sinIvaBankFeeValue = document.getElementById('sin-iva-bank-fee-value');
            const conIvaBankFeeWrap = document.getElementById('con-iva-bank-fee-wrap');
            const conIvaBankFeeValue = document.getElementById('con-iva-bank-fee-value');
            if (applyTax) {
                if (subtotalEl) subtotalEl.textContent = fmt(subtotal);
                if (ivaPctEl) ivaPctEl.textContent = fmt(taxPct);
                if (ivaAmountEl) ivaAmountEl.textContent = fmt(ivaAmount);
                if (conIvaBankFeeWrap) conIvaBankFeeWrap.classList.toggle('hidden', !applyBankFee);
                if (conIvaBankFeeValue) { conIvaBankFeeValue.textContent = fmt(bankFee); conIvaBankFeeValue.classList.toggle('hidden', !applyBankFee); }
                if (totalWithTaxEl) totalWithTaxEl.textContent = fmt(totalWithTax);
            } else {
                if (sinIvaSubtotalLabel) sinIvaSubtotalLabel.classList.toggle('hidden', !applyBankFee);
                if (sinIvaSubtotalValue) { sinIvaSubtotalValue.textContent = fmt(subtotal); sinIvaSubtotalValue.classList.toggle('hidden', !applyBankFee); }
                if (sinIvaBankFeeWrap) sinIvaBankFeeWrap.classList.toggle('hidden', !applyBankFee);
                if (sinIvaBankFeeValue) { sinIvaBankFeeValue.textContent = fmt(bankFee); sinIvaBankFeeValue.classList.toggle('hidden', !applyBankFee); }
                if (grandEl) grandEl.textContent = fmt(subtotal + bankFee);
            }
        }
        btnAdd?.addEventListener('click', function() { addRow(); });
        if (togglePrev) togglePrev.addEventListener('change', updateColumnVisibility);
        if (toggleRaa) toggleRaa.addEventListener('change', updateColumnVisibility);
        if (toggleTramite) toggleTramite.addEventListener('change', updateColumnVisibility);
        if (toggleDescription) toggleDescription.addEventListener('change', updateColumnVisibility);
        if (toggleRowId) toggleRowId.addEventListener('change', updateColumnVisibility);
        if (toggleFranquicia) toggleFranquicia.addEventListener('change', updateColumnVisibility);
        if (toggleCentroCostos) toggleCentroCostos.addEventListener('change', updateColumnVisibility);
        if (toggleContacto) toggleContacto.addEventListener('change', updateColumnVisibility);
        if (toggleApplyTax) toggleApplyTax.addEventListener('change', updateTaxSectionVisibility);
        document.getElementById('toggle-bank-fee')?.addEventListener('change', updateBankFeeVisibility);
        document.getElementById('bank_fee_value')?.addEventListener('input', updateTotals);
        document.getElementById('tax_percentage')?.addEventListener('input', updateTotals);

        var servicesDataEdit = @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'default_scope' => $s->default_scope ?? ''])->values());
        var servicesListUrl = @json(route('admin.services.list-for-quotes'));
        (function loadServicesList() {
            fetch(servicesListUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.ok ? r.json() : []; })
                .then(function(list) { if (Array.isArray(list) && list.length) servicesDataEdit = list; })
                .catch(function() {});
        })();

        var serviceDropdownEdit = null;
        var serviceDropdownHideTimerEdit = null;
        function ensureServiceDropdownEdit() {
            if (serviceDropdownEdit) return serviceDropdownEdit;
            serviceDropdownEdit = document.createElement('div');
            serviceDropdownEdit.id = 'service-suggestions-dropdown-edit';
            serviceDropdownEdit.setAttribute('role', 'listbox');
            serviceDropdownEdit.className = 'fixed z-[100] mt-1 max-h-48 overflow-auto rounded-lg border border-gray-300 bg-white shadow-lg py-1 text-sm min-w-[200px] hidden';
            document.body.appendChild(serviceDropdownEdit);
            return serviceDropdownEdit;
        }
        function showServiceSuggestionsEdit(input, row) {
            var dropdown = ensureServiceDropdownEdit();
            var val = (input.value || '').trim().toLowerCase();
            var matches = servicesDataEdit.filter(function(s) { return (s.name || '').toLowerCase().indexOf(val) !== -1; });
            dropdown.innerHTML = '';
            dropdown.classList.add('hidden');
            if (matches.length === 0) return;
            var rect = input.getBoundingClientRect();
            dropdown.style.left = rect.left + 'px';
            dropdown.style.top = (rect.bottom + 2) + 'px';
            dropdown.style.width = Math.max(rect.width, 220) + 'px';
            matches.forEach(function(s) {
                var div = document.createElement('div');
                div.setAttribute('role', 'option');
                div.className = 'px-3 py-2 cursor-pointer hover:bg-teal-50 text-gray-900';
                div.textContent = s.name;
                div.dataset.id = s.id;
                div.dataset.name = s.name;
                div.dataset.defaultScope = s.default_scope || '';
                div.addEventListener('mousedown', function(e) { e.preventDefault(); });
                div.addEventListener('click', function() {
                    input.value = s.name;
                    syncServiceInputEdit(row);
                    var desc = row.querySelector('.item-description-input');
                    var scope = row.querySelector('.item-scope-input');
                    if (desc && (!desc.value || desc.value.trim() === '')) desc.value = s.name;
                    if (scope && (!scope.value || scope.value.trim() === '')) scope.value = s.default_scope || '';
                    dropdown.classList.add('hidden');
                });
                dropdown.appendChild(div);
            });
            dropdown.classList.remove('hidden');
        }
        function hideServiceSuggestionsEdit() {
            serviceDropdownHideTimerEdit = setTimeout(function() {
                if (serviceDropdownEdit) serviceDropdownEdit.classList.add('hidden');
            }, 150);
        }

        function syncServiceInputEdit(row) {
            var input = row.querySelector('.item-service-input');
            var hidden = row.querySelector('.item-service-id-input');
            var hiddenLabel = row.querySelector('.item-service-label-input');
            if (!input || !hidden) return;
            var val = (input.value || '').trim();
            if (hiddenLabel) hiddenLabel.value = val;
            var lowerVal = val.toLowerCase();
            var found = servicesDataEdit.find(function(s) {
                var n = (s.name || '').toLowerCase();
                return n === lowerVal || (n && (lowerVal === n || lowerVal.startsWith(n + ' ') || lowerVal.startsWith(n + ' -') || lowerVal.startsWith(n + '-')));
            });
            if (found) {
                hidden.value = found.id;
                var desc = row.querySelector('.item-description-input');
                var scope = row.querySelector('.item-scope-input');
                if (desc && (!desc.value || desc.value.trim() === '')) desc.value = found.name;
                if (scope && (!scope.value || scope.value.trim() === '')) scope.value = found.default_scope || '';
            } else {
                hidden.value = '';
            }
        }
        tbody.addEventListener('input', function(e) {
            if (e.target.matches('.item-service-input')) {
                var row = e.target.closest('tr');
                syncServiceInputEdit(row);
                showServiceSuggestionsEdit(e.target, row);
            }
        });
        tbody.addEventListener('focus', function(e) {
            if (e.target.matches('.item-service-input')) {
                if (serviceDropdownHideTimerEdit) clearTimeout(serviceDropdownHideTimerEdit);
                showServiceSuggestionsEdit(e.target, e.target.closest('tr'));
            }
        }, true);
        tbody.addEventListener('blur', function(e) {
            if (e.target.matches('.item-service-input')) {
                syncServiceInputEdit(e.target.closest('tr'));
                hideServiceSuggestionsEdit();
            }
        }, true);

        document.getElementById('form-quote')?.addEventListener('submit', function() {
            tbody.querySelectorAll('.item-row').forEach(function(r) { syncServiceInputEdit(r); });
            syncColumnHiddenInputs();
        });
        tbody.querySelectorAll('.item-row').forEach(function(row) {
            const removeBtn = row.querySelector('.btn-remove-row');
            if (removeBtn) removeBtn.addEventListener('click', function() {
                if (tbody.querySelectorAll('.item-row').length <= 1) return;
                row.remove();
                rowIndex = 0;
                tbody.querySelectorAll('.item-row').forEach(function(r) {
                    r.querySelectorAll('[name^="items["]').forEach(function(el) {
                        el.name = el.name.replace(/items\[\d+\]/, 'items[' + rowIndex + ']');
                    });
                    const p = r.querySelector('.item-position-input');
                    if (p) p.value = rowIndex + 1;
                    const num = r.querySelector('.item-num');
                    if (num) num.textContent = rowIndex + 1;
                    rowIndex++;
                });
                updateTotals();
            });
            row.querySelector('.item-value').addEventListener('input', updateTotals);
        });
        updateColumnVisibility();
        updateTaxSectionVisibility();
        updateTotals();
    })();
    </script>
    @endpush
@endsection
