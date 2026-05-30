{{-- Campos de texto PDF (membrete/contexto, nota lateral, pie de cierre) --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Texto del PDF</h3>
    <p class="text-sm text-gray-600 mb-4">
        Membrete editable desde «Señor(es)…» hasta el cuerpo introductorio. Si deja un campo vacío (o igual al de la plantilla sin cambios), al descargar el PDF se usa la <strong>plantilla actual</strong>, no una copia antigua.
        Variables: <code class="bg-gray-100 px-1 rounded text-xs">{{fecha}}</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">{{ciudad}}</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">{{cliente}}</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">{{consecutivo}}</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">{{destinatario}}</code>.
    </p>
    <div class="space-y-4">
        <div>
            <label for="pdf_body_html" class="block mb-2 text-sm font-medium text-gray-900">Membrete / Contexto / Cuerpo</label>
            <textarea name="pdf_body_html" id="pdf_body_html" rows="10"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-teal-500 focus:border-teal-500">{{ old('pdf_body_html', $pdfBodyHtml ?? '') }}</textarea>
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
