{{-- Modal: invitar a registro (clientes asignados y/o correo nuevo). Incluir una vez por página. --}}
<div id="company-invite-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-900/50">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 rounded-t">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-envelope text-amber-600 mr-2"></i> Invitación para registro
                </h3>
                <button type="button" class="company-invite-modal-close text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">
                    <i class="fas fa-times"></i>
                    <span class="sr-only">Cerrar</span>
                </button>
            </div>
            <form id="company-invite-form" method="POST" action="#">
                @csrf
                <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
                    <p class="text-sm text-gray-600">
                        Empresa: <strong id="invite-modal-company-name">—</strong>
                    </p>
                    <div id="invite-modal-clients-wrap" class="hidden">
                        <p class="text-xs font-medium text-gray-700 mb-2">Seleccione uno o más clientes ya asignados a esta empresa:</p>
                        <div id="invite-modal-clients-list" class="space-y-2 border border-gray-100 rounded-lg p-3 bg-gray-50 max-h-48 overflow-y-auto"></div>
                    </div>
                    <div id="invite-modal-no-clients" class="hidden text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        No hay usuarios con rol Cliente asignados a esta empresa. Use el correo siguiente para enviar el enlace de registro (aparecerá como <strong>pendiente</strong> en Directorio → Clientes hasta que complete el formulario).
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-xs font-medium text-gray-700 mb-2">Invitar por correo (opcional)</p>
                        <p class="text-xs text-gray-500 mb-2">Útil para un contacto que aún no existe como usuario. Si ya lo seleccionó arriba, no repita el correo.</p>
                        <div class="grid grid-cols-1 gap-2">
                            <input type="email"
                                   name="invite_email"
                                   id="invite-modal-email"
                                   placeholder="correo@ejemplo.com"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                            <input type="text"
                                   name="invite_name"
                                   id="invite-modal-name"
                                   placeholder="Nombre para el correo (opcional)"
                                   maxlength="255"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-4 border-t border-gray-200 rounded-b">
                    <button type="submit" class="text-white bg-teal-600 hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5">
                        Enviar invitación(es)
                    </button>
                    <button type="button" class="company-invite-modal-close py-2.5 px-5 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    var modal = document.getElementById('company-invite-modal');
    var form = document.getElementById('company-invite-form');
    var clientsWrap = document.getElementById('invite-modal-clients-wrap');
    var noClientsMsg = document.getElementById('invite-modal-no-clients');
    var clientsList = document.getElementById('invite-modal-clients-list');
    var companyNameEl = document.getElementById('invite-modal-company-name');
    var baseUrl = @json(rtrim(route('admin.companies.index'), '/'));

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openModal() {
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    document.querySelectorAll('.company-invite-modal-close').forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.inviteConfirmed === '1') {
                return;
            }
            e.preventDefault();
            var cname = companyNameEl ? companyNameEl.textContent.trim() : '—';
            var doSubmit = function () {
                form.dataset.inviteConfirmed = '1';
                form.submit();
            };
            if (typeof Swal === 'undefined') {
                if (window.confirm('¿Enviar la(s) invitación(es) de registro para ' + cname + '?')) {
                    doSubmit();
                }
                return;
            }
            Swal.fire({
                title: '¿Enviar invitación(es)?',
                html: '<p class="text-left text-gray-600 mb-0">Se enviarán los correos de invitación al registro para la empresa <strong>' + escapeHtml(cname) + '</strong>. Revise los destinatarios seleccionados antes de confirmar.</p>',
                icon: 'question',
                showCancelButton: true,
                focusCancel: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0f766e',
            }).then(function (r) {
                if (r.isConfirmed) {
                    doSubmit();
                }
            });
        });
    }

    window.openCompanyInviteModal = function (companyId) {
        if (!form || !clientsList) return;
        delete form.dataset.inviteConfirmed;
        form.action = baseUrl + '/' + encodeURIComponent(companyId) + '/send-invite';
        form.querySelector('#invite-modal-email').value = '';
        form.querySelector('#invite-modal-name').value = '';
        clientsList.innerHTML = '';
        clientsWrap.classList.add('hidden');
        noClientsMsg.classList.add('hidden');

        fetch(baseUrl + '/' + encodeURIComponent(companyId) + '/invite-data', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                companyNameEl.textContent = data.company_name || '—';
                var clients = data.clients || [];
                if (clients.length > 0) {
                    clients.forEach(function (c) {
                        var id = 'invite-cb-' + c.id;
                        var label = document.createElement('label');
                        label.className = 'flex items-start gap-2 cursor-pointer text-sm text-gray-800';
                        label.innerHTML =
                            '<input type="checkbox" name="user_ids[]" value="' + c.id + '" id="' + id + '" class="mt-1 rounded border-gray-300 text-teal-600 focus:ring-teal-500">' +
                            '<span><span class="font-medium">' + escapeHtml(c.name) + '</span><br><span class="text-xs text-gray-500">' + escapeHtml(c.email) + '</span></span>';
                        clientsList.appendChild(label);
                    });
                    clientsWrap.classList.remove('hidden');
                } else {
                    noClientsMsg.classList.remove('hidden');
                }
                openModal();
            })
            .catch(function () {
                companyNameEl.textContent = '—';
                noClientsMsg.classList.remove('hidden');
                openModal();
            });
    };

})();
</script>
@endpush
@endonce
