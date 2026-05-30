@once
@push('scripts')
@php
    $pdfBodyTinymcePlaceholder = $pdfBodyTinymcePlaceholder ?? 'Ej: Bogotá D. C. {{fecha}}… Señor(a) {{destinatario}}… párrafo introductorio (antes de la tabla de ítems).';
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce === 'undefined' || !document.getElementById('pdf_body_html')) {
        return;
    }
    tinymce.init({
        selector: '#pdf_body_html',
        license_key: 'gpl',
        height: 320,
        menubar: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
        content_style: 'body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.45; }',
        placeholder: @json($pdfBodyTinymcePlaceholder),
        setup: function(editor) {
            editor.on('change', function() { editor.save(); });
        }
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
