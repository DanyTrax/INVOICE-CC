@php
    use App\Support\PdfDocumentHelper;
    $tpl = $defaultPdfTemplate ?? null;
    $doc = $pdfDocument ?? null;
    $bodyField = PdfDocumentHelper::resolveFormField(
        old('pdf_body_html'),
        $doc->pdf_body_html ?? null,
        $tpl?->body_html
    );
@endphp
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Texto introductorio del PDF</h3>
    <p class="text-sm text-gray-600 mb-4">
        Si queda vacío o igual a la plantilla, al descargar se usa la <strong>plantilla PDF actual</strong>.
        Variables:
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{fecha}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{ciudad}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{cliente}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{consecutivo}}@endverbatim</code>,
        <code class="bg-gray-100 px-1 rounded text-xs">@verbatim{{destinatario}}@endverbatim</code>.
        @if(empty($forShowPage))
            La nota lateral y el pie se configuran al <strong>ver</strong> el documento.
        @endif
    </p>
    <div>
        <label for="pdf_body_html" class="block mb-2 text-sm font-medium text-gray-900">Contexto / Cuerpo (texto introductorio del PDF)</label>
        @include('admin.partials.pdf-field-source-hint', ['field' => $bodyField])
        <textarea name="pdf_body_html" id="pdf_body_html" rows="12"
                  class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ $bodyField['value'] }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
            Desde «Señor(es)…» hasta el párrafo previo a la tabla de ítems. Tamaño, colores e interlineado en la barra del editor.
        </p>
    </div>
</div>

@include('admin.partials.pdf-body-tinymce-init')
