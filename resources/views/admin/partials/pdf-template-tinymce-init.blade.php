@include('admin.partials.pdf-intro-tinymce-lib')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editors = [
        { selector: '#body_html', height: 360, placeholder: @json($bodyPlaceholder ?? 'Ej: Bogotá D. C. {{fecha}}… Señor(a) {{destinatario}}…') },
        { selector: '#side_note_html', height: 220, placeholder: 'Nota lateral junto a subtotal / total…' },
        { selector: '#closing_footer_html', height: 200, placeholder: 'Pie debajo del total, encima de la firma…' }
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
