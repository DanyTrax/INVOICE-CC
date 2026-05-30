@push('scripts')
<script>
(function() {
    var clientSelect = document.getElementById('{{ $clientSelectId ?? 'client_id' }}');
    var dateInput = document.getElementById('{{ $dateInputId ?? 'date' }}');
    var consecutiveInput = document.getElementById('{{ $consecutiveInputId ?? 'consecutive' }}');
    var warnEl = document.getElementById('{{ $warnId ?? 'client_siglas_warn' }}');
    var warnLink = document.getElementById('{{ $warnLinkId ?? 'client_siglas_edit_link' }}');
    var suggestUrl = @json($suggestUrl);
    if (!clientSelect || !consecutiveInput || !suggestUrl) return;

    var consecutiveTouched = {{ ($consecutiveTouched ?? false) ? 'true' : 'false' }};
    consecutiveInput.addEventListener('input', function() { consecutiveTouched = true; });

    function yearFromDateInput() {
        if (!dateInput || !dateInput.value) return null;
        var parts = dateInput.value.split('-');
        return parts[0] ? parseInt(parts[0], 10) : null;
    }

    function selectedSiglasOk() {
        var opt = clientSelect.options[clientSelect.selectedIndex];
        return opt && opt.getAttribute('data-siglas-ok') === '1';
    }

    function updateWarn() {
        if (!warnEl) return;
        var show = clientSelect.value && !selectedSiglasOk();
        warnEl.classList.toggle('hidden', !show);
        if (warnLink && clientSelect.value) {
            warnLink.href = '/admin/companies/' + clientSelect.value + '/edit';
        }
    }

    function fetchConsecutive() {
        updateWarn();
        if (!clientSelect.value || consecutiveTouched) return;
        var params = new URLSearchParams({ client_id: clientSelect.value });
        var y = yearFromDateInput();
        if (y) params.set('year', String(y));
        fetch(suggestUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(res) {
                if (res.ok && res.data.consecutive && !consecutiveTouched) {
                    consecutiveInput.value = res.data.consecutive;
                }
            })
            .catch(function() {});
    }

    clientSelect.addEventListener('change', fetchConsecutive);
    if (dateInput) dateInput.addEventListener('change', fetchConsecutive);
    updateWarn();
    if (clientSelect.value && !consecutiveInput.value) fetchConsecutive();
})();
</script>
@endpush
