@php
    use App\Support\PdfDocumentHelper;
    $tpl = $defaultPdfTemplate ?? null;
    $doc = $pdfDocument ?? null;
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
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Nota lateral y pie del PDF</h3>
    <p class="text-sm text-gray-600 mb-4">
        Si un campo queda vacío o igual a la plantilla, al descargar se usa la <strong>plantilla PDF actual</strong>.
        Si edita y guarda texto distinto, queda solo para este documento.
    </p>
    <div class="space-y-4">
        <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <label for="pdf_side_note_html" class="text-sm font-medium text-gray-900">Nota lateral (junto a subtotal / total)</label>
                @if($pdfVisibilityToggles ?? false)
                    <input type="hidden" name="show_pdf_side_note" value="0">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="show_pdf_side_note" id="show_pdf_side_note" value="1"
                               class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 pdf-visibility-toggle"
                               data-target="pdf_side_note_html"
                               {{ old('show_pdf_side_note', $doc->show_pdf_side_note ?? true) ? 'checked' : '' }}>
                        <span>Mostrar en el PDF</span>
                    </label>
                @endif
            </div>
            @include('admin.partials.pdf-field-source-hint', ['field' => $sideField])
            <div id="pdf_side_note_html_wrap" class="pdf-field-wrap">
                <textarea name="pdf_side_note_html" id="pdf_side_note_html" rows="6"
                          class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ $sideField['value'] }}</textarea>
            </div>
            <p class="mt-1 text-xs text-gray-500">Texto a la izquierda del cuadro de totales en el PDF.</p>
        </div>
        <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <label for="pdf_footer" class="block text-sm font-medium text-gray-900">Pie de página (debajo del total, encima de firma)</label>
                @if($pdfVisibilityToggles ?? false)
                    <input type="hidden" name="show_pdf_footer" value="0">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="show_pdf_footer" id="show_pdf_footer" value="1"
                               class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 pdf-visibility-toggle"
                               data-target="pdf_footer"
                               {{ old('show_pdf_footer', $doc->show_pdf_footer ?? true) ? 'checked' : '' }}>
                        <span>Mostrar en el PDF</span>
                    </label>
                @endif
            </div>
            @include('admin.partials.pdf-field-source-hint', ['field' => $footerField])
            <div id="pdf_footer_wrap" class="pdf-field-wrap">
                <textarea name="pdf_footer" id="pdf_footer" rows="5"
                          class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500">{{ $footerField['value'] }}</textarea>
            </div>
            <p class="mt-1 text-xs text-gray-500">Aparece después del total y antes de la firma.</p>
        </div>
    </div>
</div>

@include('admin.partials.pdf-side-footer-tinymce-init')
