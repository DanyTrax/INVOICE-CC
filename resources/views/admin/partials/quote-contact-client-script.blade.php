@push('scripts')
<script>
(function() {
    var clientSelect = document.getElementById('{{ $clientSelectId ?? 'client_id' }}');
    var contactSelect = document.getElementById('{{ $contactSelectId ?? 'contact_user_id' }}');
    if (!clientSelect || !contactSelect) return;

    var companyClients = @json($companyClients ?? []);
    var preselected = String(@json((string) ($selectedContactId ?? '')));

    function populate(desiredValue) {
        var companyId = clientSelect.value;
        var current = String(desiredValue == null ? '' : desiredValue);
        var list = companyClients[companyId] || [];

        while (contactSelect.options.length > 0) contactSelect.remove(0);

        var optRepeat = document.createElement('option');
        optRepeat.value = '';
        optRepeat.textContent = 'Usar la empresa (por defecto)';
        contactSelect.appendChild(optRepeat);

        list.forEach(function(u) {
            var opt = document.createElement('option');
            opt.value = String(u.id);
            opt.textContent = u.email ? (u.name + ' — ' + u.email) : u.name;
            if (String(u.id) === current) opt.selected = true;
            contactSelect.appendChild(opt);
        });

        contactSelect.disabled = list.length === 0;
        if (list.length === 0) {
            optRepeat.textContent = companyId ? 'Sin clientes asignados — usa la empresa' : 'Elija una empresa primero';
        }
    }

    // Al cambiar de empresa se limpia la selección; en la carga inicial se respeta el contacto guardado.
    clientSelect.addEventListener('change', function() { populate(''); });
    populate(preselected);
})();
</script>
@endpush
