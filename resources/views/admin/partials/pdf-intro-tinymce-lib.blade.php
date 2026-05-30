@once('pdf-intro-tinymce-lib')
@push('scripts')
<script>
window.initPdfIntroTinyMce = function(opts) {
    opts = opts || {};
    var selector = opts.selector;
    if (!selector || typeof tinymce === 'undefined') {
        return Promise.resolve([]);
    }
    if (!document.querySelector(selector)) {
        return Promise.resolve([]);
    }

    return tinymce.init({
        selector: selector,
        license_key: 'gpl',
        height: opts.height || 360,
        menubar: false,
        branding: false,
        promotion: false,
        plugins: 'lists link code',
        toolbar: [
            'undo redo | blocks styles fontsize',
            'bold italic underline | forecolor backcolor',
            'alignleft aligncenter alignright alignjustify',
            'bullist numlist | link | removeformat code'
        ].join(' | '),
        font_size_formats: '4pt 5pt 6pt 7pt 8pt 9pt 10pt 11pt 12pt 13pt 14pt 16pt 18pt 20pt 24pt',
        style_formats: [
            { title: 'Párrafo', block: 'p' },
            { title: 'Interlineado 1.0', block: 'p', styles: { lineHeight: '1' } },
            { title: 'Interlineado 1.15', block: 'p', styles: { lineHeight: '1.15' } },
            { title: 'Interlineado 1.35', block: 'p', styles: { lineHeight: '1.35' } },
            { title: 'Interlineado 1.5', block: 'p', styles: { lineHeight: '1.5' } },
            { title: 'Interlineado 1.75', block: 'p', styles: { lineHeight: '1.75' } },
            { title: 'Interlineado 2.0', block: 'p', styles: { lineHeight: '2' } },
            { title: 'Texto muy pequeño (6pt)', inline: 'span', styles: { fontSize: '6pt' } },
            { title: 'Texto pequeño (8pt)', inline: 'span', styles: { fontSize: '8pt' } },
            { title: 'Texto grande', inline: 'span', styles: { fontSize: '14pt' } }
        ],
        color_map: [
            '1f2937', 'Texto',
            '374151', 'Gris',
            '0d9488', 'Verde',
            '0369a1', 'Azul',
            'b45309', 'Ámbar',
            'b91c1c', 'Rojo',
            '000000', 'Negro',
            'ffffff', 'Blanco'
        ],
        color_cols: 5,
        custom_colors: true,
        content_style: 'body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.45; color: #1f2937; } p { margin: 0 0 0.45em 0; }',
        placeholder: opts.placeholder || '',
        setup: function(editor) {
            editor.on('change keyup', function() {
                editor.save();
            });
        }
    });
};

window.bindPdfIntroTinyMceFormSave = function(selector) {
    var field = document.querySelector(selector);
    var form = field ? field.closest('form') : null;
    if (form) {
        form.addEventListener('submit', function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    }
};
</script>
@endpush
@endonce
