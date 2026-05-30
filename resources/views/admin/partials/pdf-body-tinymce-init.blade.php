@include('admin.partials.pdf-intro-tinymce-lib')
@once('pdf-body-tinymce-init')
@push('scripts')
@php
    $pdfBodyTinymcePlaceholder = $pdfBodyTinymcePlaceholder ?? 'Ej: Bogotá D. C. {{fecha}}… Señor(a) {{destinatario}}… párrafo introductorio (antes de la tabla de ítems).';
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('pdf_body_html')) {
        return;
    }
    window.initPdfIntroTinyMce({
        selector: '#pdf_body_html',
        height: 360,
        placeholder: @json($pdfBodyTinymcePlaceholder)
    });
    var field = document.getElementById('pdf_body_html');
    var form = field ? field.closest('form') : null;
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
