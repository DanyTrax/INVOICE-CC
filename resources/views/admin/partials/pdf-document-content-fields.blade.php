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
        Si el campo queda vacío o igual a la plantilla, al descargar se usa la <strong>plantilla PDF actual</strong>.
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
            @if($bodyField['shows_template'])
                <p class="mb-2 text-xs text-teal-800 bg-teal-50 border border-teal-100 rounded-lg px-3 py-2">
                    <i class="fas fa-info-circle mr-1"></i> Mostrando la <strong>plantilla</strong> (mismo texto que verá en el PDF). Edite y guarde para un texto propio de este documento.
                </p>
            @elseif($bodyField['is_override'])
                <p class="mb-2 text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                    <i class="fas fa-edit mr-1"></i> <strong>Texto de este documento</strong> (la plantilla no se usa en este campo). Borre todo y guarde para volver a la plantilla.
                </p>
            @endif
            <textarea name="pdf_body_html" id="pdf_body_html" rows="12"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ $bodyField['value'] }}</textarea>
            <p class="mt-1 text-xs text-gray-500">
                Desde «Señor(es)…» hasta el párrafo previo a la tabla de ítems. La tabla de servicios y totales no se edita aquí.
                El membrete gráfico (imagen de fondo) se configura en la plantilla PDF.
            </p>
        </div>
        <div>
            <label for="pdf_side_note_html" class="block mb-2 text-sm font-medium text-gray-900">Nota lateral (junto a totales)</label>
            <textarea name="pdf_side_note_html" id="pdf_side_note_html" rows="5"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-teal-500 focus:border-teal-500">{{ $sideField['value'] }}</textarea>
        </div>
        <div>
            <label for="pdf_footer" class="block mb-2 text-sm font-medium text-gray-900">Pie de página (debajo del total, encima de firma)</label>
            <textarea name="pdf_footer" id="pdf_footer" rows="4" maxlength="2000"
                      class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-teal-500 focus:border-teal-500">{{ $footerField['value'] }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Vacío o igual a plantilla → se usa el pie de la plantilla actual al descargar.</p>
        </div>
    </div>
</div>

@include('admin.partials.pdf-body-tinymce-init')
