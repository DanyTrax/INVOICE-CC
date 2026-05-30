@php $template = $template ?? null; @endphp
@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nombre de la plantilla <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}" required
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
               placeholder="Ej: Plantilla Doble Vía">
    </div>

    <div class="md:col-span-2">
        <label for="letterhead" class="block mb-2 text-sm font-medium text-gray-900">Membrete (imagen de fondo del PDF)</label>
        @php
            $letterheadPath = $template->letterhead_path ?? $template->logo_path ?? null;
        @endphp
        @if($template && $letterheadPath && file_exists(public_path($letterheadPath)))
            <div class="mb-2">
                <img src="{{ asset($letterheadPath) }}" alt="Membrete actual" class="max-h-40 w-full object-contain border border-gray-200 rounded-lg p-2 bg-white">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remove_letterhead" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                <span>Eliminar membrete actual</span>
            </label>
            <span class="mx-2">|</span>
        @endif
        <input type="file" name="letterhead" id="letterhead" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 mt-2">
        <p class="mt-1 text-xs text-gray-500">Imagen completa membreteada (cabecera y pie incluidos en el diseño). JPG, PNG, GIF, SVG o WEBP. Máx. 5MB. Se adapta al ancho de la hoja.</p>
    </div>

    <div class="md:col-span-2">
        <label for="body_html" class="block mb-2 text-sm font-medium text-gray-900">Contexto / Cuerpo (texto introductorio del PDF)</label>
        <textarea name="body_html" id="body_html" rows="12" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('body_html', $template->body_html ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
            Desde «Señor(es)…» hasta el párrafo previo a la tabla. Variables:
            <code class="bg-gray-100 px-1 rounded">@verbatim {{fecha}} @endverbatim</code>,
            <code class="bg-gray-100 px-1 rounded">@verbatim {{ciudad}} @endverbatim</code>,
            <code class="bg-gray-100 px-1 rounded">@verbatim {{cliente}} @endverbatim</code>,
            <code class="bg-gray-100 px-1 rounded">@verbatim {{consecutivo}} @endverbatim</code>,
            <code class="bg-gray-100 px-1 rounded">@verbatim {{destinatario}} @endverbatim</code>.
            Al crear la cotización/propuesta se puede editar manualmente.
        </p>
    </div>

    <div class="md:col-span-2">
        <label for="side_note_html" class="block mb-2 text-sm font-medium text-gray-900">Nota lateral (junto a subtotal / total)</label>
        <textarea name="side_note_html" id="side_note_html" rows="6" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('side_note_html', $template->side_note_html ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Editor con tamaño, colores e interlineado (menú Estilos).</p>
        <p class="mt-1 text-xs text-gray-500">Texto libre en el espacio a la izquierda de los totales. Acepta las mismas variables.</p>
    </div>

    <div class="md:col-span-2">
        <label for="closing_footer_html" class="block mb-2 text-sm font-medium text-gray-900">Pie de página (debajo del total, encima de la firma)</label>
        <textarea name="closing_footer_html" id="closing_footer_html" rows="5" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('closing_footer_html', $template->closing_footer_html ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Editor con tamaño, colores e interlineado (menú Estilos).</p>
        <p class="mt-1 text-xs text-gray-500">Aparece al final del documento, después del total y antes de la línea de firma.</p>
    </div>

    <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-2">
        <p class="text-sm font-medium text-gray-900 mb-3">Firma (aparece al final del PDF)</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="signature_name" class="block mb-2 text-sm font-medium text-gray-900">Nombre (debajo de la línea)</label>
                <input type="text" name="signature_name" id="signature_name" value="{{ old('signature_name', $template->signature_name ?? '') }}"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
                       placeholder="Ej: Mónica Zamorano Rubio">
            </div>
            <div>
                <label for="signature_position" class="block mb-2 text-sm font-medium text-gray-900">Cargo</label>
                <input type="text" name="signature_position" id="signature_position" value="{{ old('signature_position', $template->signature_position ?? '') }}"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
                       placeholder="Ej: Gerente General">
            </div>
            <div>
                <label for="signature_name_font_size" class="block mb-2 text-sm font-medium text-gray-900">Tamaño texto — Nombre (px)</label>
                <input type="number" name="signature_name_font_size" id="signature_name_font_size" min="8" max="24"
                       value="{{ old('signature_name_font_size', $template->signature_name_font_size ?? 11) }}"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
            </div>
            <div>
                <label for="signature_position_font_size" class="block mb-2 text-sm font-medium text-gray-900">Tamaño texto — Cargo (px)</label>
                <input type="number" name="signature_position_font_size" id="signature_position_font_size" min="8" max="24"
                       value="{{ old('signature_position_font_size', $template->signature_position_font_size ?? 11) }}"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
            </div>
            <div>
                <label for="signature_margin_top_px" class="block mb-2 text-sm font-medium text-gray-900">Espacio sobre la firma (px)</label>
                <input type="number" name="signature_margin_top_px" id="signature_margin_top_px" min="0" max="400"
                       value="{{ old('signature_margin_top_px', $template->signature_margin_top_px ?? 130) }}"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                <p class="mt-1 text-xs text-gray-500">Aumente para bajar la firma hacia el pie de página (sin invadir el gráfico del membrete).</p>
            </div>
            <div>
                <label for="letterhead_footer_reserve_mm" class="block mb-2 text-sm font-medium text-gray-900">Margen inferior seguro (mm)</label>
                <input type="number" name="letterhead_footer_reserve_mm" id="letterhead_footer_reserve_mm" min="20" max="80"
                       value="{{ old('letterhead_footer_reserve_mm', $template->letterhead_footer_reserve_mm ?? 42) }}"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                <p class="mt-1 text-xs text-gray-500">Reserva en la parte baja para datos de contacto / ondas del membrete (por defecto 42).</p>
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('is_default', $template->is_default ?? false) ? 'checked' : '' }}>
            <span class="text-sm font-medium text-gray-900">{{ $defaultLabel ?? 'Usar como plantilla por defecto al generar PDF' }}</span>
        </label>
    </div>
</div>
