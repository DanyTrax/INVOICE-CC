@include('admin.partials.pdf-intro-tinymce-lib')
@once('pdf-document-tinymce-init')
@push('scripts')
@php
    $pdfBodyTinymcePlaceholder = $pdfBodyTinymcePlaceholder ?? 'Ej: Bogotá D. C. {{fecha}}… Señor(a) {{destinatario}}… párrafo introductorio (antes de la tabla de ítems).';
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editors = [
        { selector: '#pdf_body_html', height: 360, placeholder: @json($pdfBodyTinymcePlaceholder) },
        { selector: '#pdf_side_note_html', height: 220, placeholder: 'Nota junto a subtotal / total (ej. tarifas INVIMA)…' },
        { selector: '#pdf_footer', height: 200, placeholder: 'Pie debajo del total, encima de la firma…' }
    ];
    var form = null;
    editors.forEach(function(cfg) {
        if (!document.querySelector(cfg.selector)) {
            return;
        }
        if (!form) {
            form = document.querySelector(cfg.selector).closest('form');
        }
        window.initPdfIntroTinyMce(cfg);
    });
    if (form) {
        form.addEventListener('submit', function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    }
});
</script>
@endpush
@endonce
