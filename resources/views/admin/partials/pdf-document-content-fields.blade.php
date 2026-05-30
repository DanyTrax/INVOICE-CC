@php
    use App\Support\PdfDocumentHelper;
    $tpl = $defaultPdfTemplate ?? null;
    $doc = $pdfDocument ?? null;
    $bodyField = PdfDocumentHelper::resolveFormField(
        old('pdf_body_html'),
        $doc->pdf_body_html ?? null,
        $tpl?->body_html
    );
    $sideField = PdfDocumentHelper::resolveFormField(
        old('pdf_side_note_html'),
        $doc->pdf_side_note_html ?? null,
        $tpl?->side_note_html
    );
    $footerField = PdfDocumentHelper::resolveFormField(
        old('pdf_footer'),
        $doc->pdf_footer ?? null,
        $tpl?->closing_footer_html
    );
@endphp
{{-- Campos de texto PDF (contexto introductorio, nota lateral, pie de cierre) --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Texto del PDF</h3>
    <p class="text-sm text-gray-600 mb-4">
        Si un campo queda vacío o igual a la plantilla, al descargar se usa la <strong>plantilla PDF actual</strong>.
        Si edita y guarda texto distinto, queda solo para esta cotización/propuesta.
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
            @include('admin.partials.pdf-field-source-hint', ['field' => $bodyField])
            <textarea name="pdf_body_html" id="pdf_body_html" rows="12"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ $bodyField['value'] }}</textarea>
            <p class="mt-1 text-xs text-gray-500">
                Desde «Señor(es)…» hasta el párrafo previo a la tabla de ítems. Tamaño, colores e interlineado en la barra del editor.
                El membrete gráfico (imagen de fondo) se configura en la plantilla PDF.
            </p>
        </div>
        <div>
            <label for="pdf_side_note_html" class="block mb-2 text-sm font-medium text-gray-900">Nota lateral (junto a subtotal / total)</label>
            @include('admin.partials.pdf-field-source-hint', ['field' => $sideField])
            <textarea name="pdf_side_note_html" id="pdf_side_note_html" rows="6"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ $sideField['value'] }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Texto a la izquierda del cuadro de totales en el PDF.</p>
        </div>
        <div>
            <label for="pdf_footer" class="block mb-2 text-sm font-medium text-gray-900">Pie de página (debajo del total, encima de firma)</label>
            @include('admin.partials.pdf-field-source-hint', ['field' => $footerField])
            <textarea name="pdf_footer" id="pdf_footer" rows="5"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ $footerField['value'] }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Aparece después del total y antes de la firma.</p>
        </div>
    </div>
</div>

@include('admin.partials.pdf-document-tinymce-init')
