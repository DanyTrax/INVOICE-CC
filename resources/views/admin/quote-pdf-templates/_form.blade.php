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
        <label for="logo" class="block mb-2 text-sm font-medium text-gray-900">Logo (cabecera del PDF)</label>
        @if($template && $template->logo_path && file_exists(public_path($template->logo_path)))
            <div class="mb-2">
                <img src="{{ asset($template->logo_path) }}" alt="Logo actual" class="h-16 w-auto object-contain border border-gray-200 rounded-lg p-2 bg-white">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                <span>Eliminar logo actual</span>
            </label>
            <span class="mx-2">|</span>
        @endif
        <input type="file" name="logo" id="logo" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 mt-2">
        <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF, SVG o WEBP. Máx. 2MB.</p>
    </div>

    <div>
        <label for="header_company_name" class="block mb-2 text-sm font-medium text-gray-900">Nombre empresa (cabecera)</label>
        <input type="text" name="header_company_name" id="header_company_name" value="{{ old('header_company_name', $template->header_company_name ?? '') }}"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
               placeholder="Ej: ASESORIAS Y CONSULTORIAS DOBLE VÍA S.A.S.">
    </div>
    <div>
        <label for="header_nit" class="block mb-2 text-sm font-medium text-gray-900">NIT (cabecera)</label>
        <input type="text" name="header_nit" id="header_nit" value="{{ old('header_nit', $template->header_nit ?? '') }}"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
               placeholder="Ej: NIT. 900.589.747-6">
    </div>
    <div class="md:col-span-2">
        <label for="header_subtitle" class="block mb-2 text-sm font-medium text-gray-900">Subtítulo / Slogan (cabecera)</label>
        <input type="text" name="header_subtitle" id="header_subtitle" value="{{ old('header_subtitle', $template->header_subtitle ?? '') }}"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
               placeholder="Ej: La vía para el crecimiento de su empresa">
    </div>

    <div class="md:col-span-2">
        <label for="body_html" class="block mb-2 text-sm font-medium text-gray-900">Contexto / Cuerpo (texto introductorio del PDF)</label>
        <textarea name="body_html" id="body_html" rows="12" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">{{ old('body_html', $template->body_html ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
            Use variables: <code class="bg-gray-100 px-1 rounded">{{ '{{fecha}}' }}</code> (fecha), <code class="bg-gray-100 px-1 rounded">{{ '{{ciudad}}' }}</code>, <code class="bg-gray-100 px-1 rounded">{{ '{{cliente}}' }}</code>, <code class="bg-gray-100 px-1 rounded">{{ '{{consecutivo}}' }}</code>, <code class="bg-gray-100 px-1 rounded">{{ '{{destinatario}}' }}</code> (nombre del cliente). Se reemplazarán al generar el PDF.
        </p>
    </div>

    <div class="md:col-span-2">
        <label for="footer_text" class="block mb-2 text-sm font-medium text-gray-900">Pie de página (PDF)</label>
        <input type="text" name="footer_text" id="footer_text" value="{{ old('footer_text', $template->footer_text ?? '') }}"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
               placeholder="Ej: RAMS - Regulatory Affairs Management System">
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ old('is_default', $template->is_default ?? false) ? 'checked' : '' }}>
            <span class="text-sm font-medium text-gray-900">Usar como plantilla por defecto al generar PDF</span>
        </label>
    </div>
</div>
