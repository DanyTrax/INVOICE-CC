{{-- Selector de país: escribir para buscar (sin importar tildes) y elegir solo de la lista --}}
@php
    $countries = $countries ?? config('countries', []);
    $sorted = collect($countries)->sort()->values()->all();
    $value = old('country', $value ?? '');
    $inputId = $inputId ?? 'country';
    $inputName = $inputName ?? 'country';
@endphp
<div class="country-selector relative" data-countries="{{ json_encode($sorted) }}">
    <input type="hidden" name="{{ $inputName }}" id="{{ $inputId }}" value="{{ $value }}">
    <input type="text"
           id="{{ $inputId }}_search"
           value="{{ $value }}"
           placeholder="Escriba para buscar (ej: col, mex)..."
           autocomplete="off"
           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5"
           aria-label="Buscar país">
    <div id="{{ $inputId }}_list" class="hidden absolute z-20 mt-1 w-full max-h-56 overflow-auto bg-white border border-gray-300 rounded-lg shadow-lg"></div>
</div>
<script>
(function() {
    function normalize(s) {
        if (!s) return '';
        return String(s).normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();
    }
    document.querySelectorAll('.country-selector').forEach(function(wrap) {
        var countries = JSON.parse(wrap.getAttribute('data-countries') || '[]');
        var hiddenInput = wrap.querySelector('input[type="hidden"]');
        var searchInput = wrap.querySelector('input[type="text"]');
        var listDiv = wrap.querySelector('div[id$="_list"]');
        if (!hiddenInput || !searchInput || !listDiv) return;

        function escapeHtml(s) {
            var d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }
        function showList(filter) {
            var q = normalize(filter || '');
            var html = '';
            countries.forEach(function(c) {
                if (!q || normalize(c).indexOf(q) !== -1) {
                    html += '<button type="button" class="country-option block w-full text-left px-3 py-2 text-sm text-gray-900 hover:bg-teal-50 focus:bg-teal-50 focus:outline-none" data-value="' + c.replace(/"/g, '&quot;') + '">' + escapeHtml(c) + '</button>';
                }
            });
            listDiv.innerHTML = html || '<p class="px-3 py-2 text-sm text-gray-500">No hay coincidencias. Elija un país de la lista.</p>';
            listDiv.classList.remove('hidden');
            listDiv.querySelectorAll('.country-option').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var v = this.getAttribute('data-value');
                    hiddenInput.value = v;
                    searchInput.value = v;
                    listDiv.classList.add('hidden');
                    searchInput.focus();
                });
            });
        }

        function hideList() {
            setTimeout(function() { listDiv.classList.add('hidden'); }, 200);
        }

        searchInput.addEventListener('input', function() {
            showList(this.value);
        });
        searchInput.addEventListener('focus', function() {
            showList(this.value);
        });
        searchInput.addEventListener('blur', hideList);
        listDiv.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });
    });
})();
</script>
