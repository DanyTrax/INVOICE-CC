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
        placeholder: @json($pdfBodyTinymcePlaceholder)
    });
    window.bindPdfIntroTinyMceFormSave('#pdf_body_html');
});
</script>
@endpush
@endonce
