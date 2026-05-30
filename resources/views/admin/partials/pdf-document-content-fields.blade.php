{{-- Campos de texto PDF (contexto introductorio, nota lateral, pie de cierre) --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Texto del PDF</h3>
    <p class="text-sm text-gray-600 mb-4">
        Si deja un campo vacío (o igual al de la plantilla sin cambios), al descargar se usa la <strong>plantilla actual</strong>.
        Variables:
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{fecha}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{ciudad}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{cliente}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{consecutivo}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{destinatario}}@endverbatim</code>.
    </p>
    <div class="space-y-4">
        <div>
            <label for="pdf_body_html" class="block mb-2 text-sm font-medium text-gray-900">Contexto / Cuerpo (texto introductorio del PDF)</label>
            <textarea name="pdf_body_html" id="pdf_body_html" rows="12"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ old('pdf_body_html', $pdfBodyHtml ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">
                Desde «Señor(es)…» hasta el párrafo previo a la tabla de ítems. La tabla de servicios y totales no se edita aquí.
                El membrete gráfico (imagen de fondo) se configura en la plantilla PDF.
            </p>
        </div>
        <div>
            <label for="pdf_side_note_html" class="block mb-2 text-sm font-medium text-gray-900">Nota lateral (junto a totales)</label>
            <textarea name="pdf_side_note_html" id="pdf_side_note_html" rows="5"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-teal-500 focus:border-teal-500">{{ old('pdf_side_note_html', $pdfSideNoteHtml ?? '') }}</textarea>
        </div>
        <div>
            <label for="pdf_footer" class="block mb-2 text-sm font-medium text-gray-900">Pie de página (debajo del total, encima de firma)</label>
            <textarea name="pdf_footer" id="pdf_footer" rows="4" maxlength="2000"
                      placeholder="Vacío = pie de la plantilla PDF actual"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-teal-500 focus:border-teal-500">{{ old('pdf_footer', $pdfFooter ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Texto personalizado solo para este documento. Borre el contenido y guarde para volver a usar la plantilla.</p>
        </div>
    </div>
</div>

@include('admin.partials.pdf-body-tinymce-init')
