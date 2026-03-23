{{-- Modal asignación de equipo (solo admin/super_admin disparan openProcessAssignmentModal). --}}
<div id="process-assignment-modal" class="hidden fixed inset-0 z-[120] overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60" onclick="closeProcessAssignmentModal()"></div>
        <div class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl border border-gray-200" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Asignar equipo al expediente</h3>
                    <p class="text-xs text-gray-500 mt-1" id="pam-subtitle">—</p>
                </div>
                <button type="button" onclick="closeProcessAssignmentModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="px-5 py-4 max-h-[70vh] overflow-y-auto">
                <p class="text-sm text-gray-600 mb-3">Seleccione usuarios que podrán ver este expediente. Marque si pueden alimentar la línea de tiempo o gestionar documentos / Drive (según permisos del rol).</p>
                <div id="pam-loading" class="py-8 text-center text-gray-500">
                    <i class="fas fa-circle-notch fa-spin text-2xl text-teal-600"></i>
                    <p class="mt-2 text-sm">Cargando…</p>
                </div>
                <div id="pam-error" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"></div>
                <div id="pam-body" class="hidden space-y-3"></div>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 px-5 py-4 bg-gray-50 rounded-b-xl">
                <button type="button" onclick="closeProcessAssignmentModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button type="button" id="pam-save" onclick="saveProcessAssignment()" class="hidden px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700">Guardar</button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function() {
    var modal = document.getElementById('process-assignment-modal');
    var subtitle = document.getElementById('pam-subtitle');
    var loading = document.getElementById('pam-loading');
    var body = document.getElementById('pam-body');
    var errEl = document.getElementById('pam-error');
    var saveBtn = document.getElementById('pam-save');
    var currentProcessId = null;
    var usersPayload = [];

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    window.openProcessAssignmentModal = function(processId) {
        currentProcessId = processId;
        errEl.classList.add('hidden');
        errEl.textContent = '';
        body.classList.add('hidden');
        body.innerHTML = '';
        saveBtn.classList.add('hidden');
        loading.classList.remove('hidden');
        modal.classList.remove('hidden');

        fetch('{{ url('/admin/processes') }}/' + processId + '/assignments', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, data: j }; }); })
        .then(function(res) {
            loading.classList.add('hidden');
            if (!res.ok) {
                errEl.textContent = res.data.message || 'No se pudo cargar.';
                errEl.classList.remove('hidden');
                return;
            }
            var d = res.data;
            subtitle.textContent = '#' + d.process_id + ' · ' + (d.product_reference || '—') + ' — ' + (d.client_name || '');
            usersPayload = d.users || [];
            var sel = {};
            var assignments = (d.assignments && typeof d.assignments === 'object') ? d.assignments : {};
            usersPayload.forEach(function(u) {
                var a = assignments[u.id] || {};
                sel[u.id] = {
                    checked: (d.user_ids || []).indexOf(u.id) !== -1,
                    feed: !!a.can_feed_timeline,
                    docs: !!a.can_manage_documents,
                    offerT: u.can_offer_timeline,
                    offerD: u.can_offer_documents
                };
            });
            var html = '';
            usersPayload.forEach(function(u) {
                var s = sel[u.id] || { checked: false, feed: false, docs: false, offerT: u.can_offer_timeline, offerD: u.can_offer_documents };
                var id = u.id;
                html += '<div class="rounded-lg border border-gray-200 p-3 bg-gray-50/80">';
                html += '<label class="flex items-start gap-2 cursor-pointer">';
                html += '<input type="checkbox" class="pam-user mt-1 rounded border-gray-300 text-teal-600 focus:ring-teal-500" data-uid="' + id + '" ' + (s.checked ? 'checked' : '') + ' onchange="pamToggleUser(' + id + ')">';
                html += '<span><span class="font-medium text-gray-900">' + escapeHtml(u.name) + '</span><br><span class="text-xs text-gray-500">' + escapeHtml(u.email) + '</span></span>';
                html += '</label>';
                html += '<div class="mt-2 ml-7 flex flex-wrap gap-4 text-sm pam-flags" data-uid="' + id + '" style="' + (s.checked ? '' : 'opacity:0.5;pointer-events:none;') + '">';
                html += '<label class="inline-flex items-center gap-2 ' + (!s.offerT ? 'text-gray-400' : '') + '">';
                html += '<input type="checkbox" class="pam-feed rounded border-gray-300 text-teal-600" data-uid="' + id + '" ' + (s.feed ? 'checked' : '') + ' ' + (!s.offerT ? 'disabled' : '') + '>';
                html += '<span>Línea de tiempo</span></label>';
                html += '<label class="inline-flex items-center gap-2 ' + (!s.offerD ? 'text-gray-400' : '') + '">';
                html += '<input type="checkbox" class="pam-docs rounded border-gray-300 text-teal-600" data-uid="' + id + '" ' + (s.docs ? 'checked' : '') + ' ' + (!s.offerD ? 'disabled' : '') + '>';
                html += '<span>Documentos / Drive</span></label>';
                html += '</div></div>';
            });
            if (!html) {
                html = '<p class="text-sm text-gray-500">No hay usuarios disponibles para asignar.</p>';
            }
            body.innerHTML = html;
            body.classList.remove('hidden');
            saveBtn.classList.remove('hidden');
        })
        .catch(function() {
            loading.classList.add('hidden');
            errEl.textContent = 'Error de red.';
            errEl.classList.remove('hidden');
        });
    };

    window.pamToggleUser = function(uid) {
        var box = modal.querySelector('.pam-user[data-uid="' + uid + '"]');
        var flags = modal.querySelector('.pam-flags[data-uid="' + uid + '"]');
        if (!flags) return;
        if (box && box.checked) {
            flags.style.opacity = '1';
            flags.style.pointerEvents = '';
        } else {
            flags.style.opacity = '0.5';
            flags.style.pointerEvents = 'none';
        }
    };

    function escapeHtml(t) {
        if (!t) return '';
        var d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    window.closeProcessAssignmentModal = function() {
        modal.classList.add('hidden');
        currentProcessId = null;
    };

    window.saveProcessAssignment = function() {
        if (!currentProcessId) return;
        errEl.classList.add('hidden');
        var assignments = [];
        usersPayload.forEach(function(u) {
            var cb = modal.querySelector('.pam-user[data-uid="' + u.id + '"]');
            if (!cb || !cb.checked) return;
            var feed = modal.querySelector('.pam-feed[data-uid="' + u.id + '"]');
            var docs = modal.querySelector('.pam-docs[data-uid="' + u.id + '"]');
            assignments.push({
                user_id: u.id,
                can_feed_timeline: !!(feed && feed.checked),
                can_manage_documents: !!(docs && docs.checked)
            });
        });
        fetch('{{ url('/admin/processes') }}/' + currentProcessId + '/assignments', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ assignments: assignments })
        })
        .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, data: j }; }); })
        .then(function(res) {
            if (!res.ok) {
                errEl.textContent = (res.data && res.data.message) ? res.data.message : 'Error al guardar.';
                errEl.classList.remove('hidden');
                return;
            }
            closeProcessAssignmentModal();
            if (typeof window.reloadMonitorRows === 'function') window.reloadMonitorRows();
            else window.location.reload();
        })
        .catch(function() {
            errEl.textContent = 'Error de red.';
            errEl.classList.remove('hidden');
        });
    };
})();
</script>
@endpush
@endonce
