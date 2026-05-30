@include('admin.partials.pdf-intro-tinymce-lib')
@push('scripts')
@php
    $tinymcePlaceholder = $tinymcePlaceholder ?? 'Ej: Bogotá D. C. {{fecha}}… Señor(a) {{destinatario}}…';
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('body_html')) {
        return;
    }
    window.initPdfIntroTinyMce({
        selector: '#body_html',
        placeholder: @json($tinymcePlaceholder)
    });
    window.bindPdfIntroTinyMceFormSave('#body_html');
});
</script>
@endpush
