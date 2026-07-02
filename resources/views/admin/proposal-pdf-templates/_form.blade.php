@include('admin.quote-pdf-templates._form', [
    'template' => $template ?? null,
    'defaultLabel' => 'Usar como plantilla por defecto al generar PDF de propuestas',
    'docTitleExample' => 'PROPUESTA No.',
])
