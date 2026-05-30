@if($field['shows_template'] ?? false)
    <p class="mb-2 text-xs text-teal-800 bg-teal-50 border border-teal-100 rounded-lg px-3 py-2">
        <i class="fas fa-info-circle mr-1"></i> Mostrando la <strong>plantilla</strong>. Edite y guarde para un texto propio de este documento.
    </p>
@elseif($field['is_override'] ?? false)
    <p class="mb-2 text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
        <i class="fas fa-edit mr-1"></i> <strong>Texto de este documento</strong>. Borre todo y guarde para volver a la plantilla.
    </p>
@endif
