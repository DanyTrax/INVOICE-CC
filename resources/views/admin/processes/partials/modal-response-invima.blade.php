<div id="modal-response-invima" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('modal-response-invima').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-reply text-teal-600 mr-2"></i> Registrar Respuesta INVIMA</h3>
                <p class="text-sm text-gray-500 mt-1">Sometimiento: {{ $submission->submission_code ?? $submission->radicado_invima ?? '#' . $submission->id }}</p>
            </div>
            {{-- Tabs --}}
            <div class="flex border-b border-gray-200">
                <button type="button" id="tab-auto" data-tab="auto" class="response-tab flex-1 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 data-[active=true]:bg-teal-50 data-[active=true]:text-teal-700 data-[active=true]:border-b-2 data-[active=true]:border-teal-600">
                    Auto / Requerimiento
                </button>
                <button type="button" id="tab-aprobado" data-tab="aprobado" class="response-tab flex-1 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 data-[active=true]:bg-teal-50 data-[active=true]:text-teal-700 data-[active=true]:border-b-2 data-[active=true]:border-teal-600">
                    Resolución Aprobatoria
                </button>
                <button type="button" id="tab-rechazo" data-tab="rechazo" class="response-tab flex-1 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 data-[active=true]:bg-teal-50 data-[active=true]:text-teal-700 data-[active=true]:border-b-2 data-[active=true]:border-teal-600">
                    Rechazo
                </button>
            </div>
            <div class="p-6">
                <form id="form-response-invima" action="{{ route('admin.submissions.register-response', $submission) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="response_type" id="response_type" value="auto">

                    <div id="panel-auto" class="response-panel space-y-4">
                        <p class="text-sm text-gray-600">Se registrará un Auto. Fecha límite = notificación + 90 días hábiles. El expediente pasará a <strong>En Requerimiento</strong>.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número del Auto <span class="text-red-500">*</span></label>
                            <input type="text" name="document_number" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej: AUTO-2025-123">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de notificación <span class="text-red-500">*</span></label>
                            <input type="date" name="notification_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div id="panel-aprobado" class="response-panel hidden space-y-4">
                        <p class="text-sm text-gray-600">Se registrará la Resolución aprobatoria. El expediente pasará a <strong>Finalizado</strong>. Complete Radicado y Llave.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Radicado <span class="text-red-500">*</span></label>
                            <input type="text" name="resolution_number" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Número de radicado">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Resolución <span class="text-red-500">*</span></label>
                            <input type="date" name="resolution_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Llave <span class="text-red-500">*</span></label>
                            <input type="text" name="resolution_key" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej: 2025-12345">
                        </div>
                    </div>
                    <div id="panel-file" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">PDF (opcional)</label>
                        <input type="file" name="file" accept=".pdf" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700">
                    </div>

                    <div id="panel-rechazo" class="response-panel hidden space-y-4">
                        <p class="text-sm text-gray-600">Se marcará el sometimiento como <strong>Rechazado</strong>. Indique el motivo. Luego podrá crear un nuevo intento desde &quot;Crear Nuevo Intento&quot;.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observación (motivo del rechazo) <span class="text-red-500">*</span></label>
                            <textarea name="rejection_observation" id="rejection_observation" rows="4" maxlength="2000" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Describa el motivo del rechazo para informar al cliente."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-response-invima').classList.add('hidden')" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var form = document.getElementById('form-response-invima');
        var typeInput = document.getElementById('response_type');
        var tabs = document.querySelectorAll('.response-tab');
        var panels = document.querySelectorAll('.response-panel');
        var panelFile = document.getElementById('panel-file');
        function showPanel(type) {
            typeInput.value = type;
            tabs.forEach(function(t) { t.dataset.active = t.dataset.tab === type ? 'true' : 'false'; });
            panels.forEach(function(p) {
                var id = p.id;
                if ((id === 'panel-auto' && type === 'auto') || (id === 'panel-aprobado' && type === 'aprobado') || (id === 'panel-rechazo' && type === 'rechazo')) p.classList.remove('hidden'); else p.classList.add('hidden');
            });
            if (panelFile) panelFile.classList.toggle('hidden', type === 'rechazo');
        }
        tabs.forEach(function(t) {
            t.addEventListener('click', function() { showPanel(this.dataset.tab); });
        });
        form.addEventListener('submit', function(e) {
            if (typeInput.value === 'rechazo') {
                var obs = document.getElementById('rejection_observation');
                if (!obs || !obs.value.trim()) {
                    e.preventDefault();
                    alert('Debe indicar la observación (motivo del rechazo).');
                    obs && obs.focus();
                    return false;
                }
            }
        });
        showPanel('auto');
    })();
    </script>
</div>
