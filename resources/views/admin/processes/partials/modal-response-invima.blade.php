<div id="modal-response-invima" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('modal-response-invima').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-reply text-teal-600 mr-2"></i> Registrar Respuesta INVIMA</h3>
                <p class="text-sm text-gray-500 mt-1">Sometimiento: {{ $submission->submission_code ?? $submission->radicado_invima ?? '#' . $submission->id }}</p>
            </div>
            {{-- Tabs (ocultos al abrir desde Aprobar/Rechazar/Auto: solo se muestra la opción elegida) --}}
            <div id="response-modal-tabs" class="flex border-b border-gray-200" style="display: none;">
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

                    {{-- Aprobar (Pendiente): registrar datos del radicado; se crea línea "Radicado" en la timeline con los dos botones --}}
                    <div id="panel-radicado" class="response-panel hidden space-y-4">
                        <p class="text-sm text-gray-600">Se aprobará el sometimiento y se creará una línea <strong>Radicado</strong> en la línea de tiempo con los datos que ingrese. Luego podrá registrar REQUERIMIENTO AUTO o RESOLUCIÓN desde esa línea.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de radicado <span class="text-red-500">*</span></label>
                            <input type="text" name="radicado_invima" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Número de radicado INVIMA">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de radicado <span class="text-red-500">*</span></label>
                            <input type="date" name="fecha_radicacion" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Llave / Campo de registro <span class="text-red-500">*</span></label>
                            <input type="text" name="resolution_key" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Registro / Llave">
                        </div>
                    </div>

                    <div id="panel-auto" class="response-panel hidden space-y-4">
                        <p class="text-sm text-gray-600">Se registrará un Requerimiento AUTO. Se cierra este ciclo y se crea uno nuevo. El expediente pasará a <strong>En Requerimiento</strong>.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de AUTO <span class="text-red-500">*</span></label>
                            <input type="text" name="document_number" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej: AUTO-2025-123">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de AUTO <span class="text-red-500">*</span></label>
                            <input type="date" name="notification_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Fecha límite">
                        </div>
                    </div>

                    <div id="panel-aprobado" class="response-panel hidden space-y-4">
                        <p class="text-sm text-gray-600">Se registrará la Resolución aprobatoria. El expediente pasará a <strong>Finalizado</strong>.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de resolución <span class="text-red-500">*</span></label>
                            <input type="text" name="resolution_number" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Número de resolución">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de resolución <span class="text-red-500">*</span></label>
                            <input type="date" name="resolution_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Campo de registro <span class="text-red-500">*</span></label>
                            <input type="text" name="resolution_key" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Registro / Llave">
                        </div>
                    </div>
                    <div id="panel-file" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">PDF (opcional)</label>
                        <input type="file" name="file" accept=".pdf" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700">
                    </div>

                    <div id="panel-rechazo" class="response-panel hidden space-y-4">
                        <p class="text-sm text-gray-600">Se marcará el sometimiento como <strong>Rechazado</strong>. Indique el motivo. Puede crear <strong>más intentos en el mismo ciclo</strong> desde &quot;Crear Nuevo Intento&quot; (vinculando a este intento).</p>
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
        var tabsContainer = document.getElementById('response-modal-tabs');
        var tabs = document.querySelectorAll('.response-tab');
        var panels = document.querySelectorAll('.response-panel');
        var panelFile = document.getElementById('panel-file');
        function showPanel(type) {
            typeInput.value = type;
            if (tabs) tabs.forEach(function(t) { t.dataset.active = t.dataset.tab === type ? 'true' : 'false'; });
            panels.forEach(function(p) {
                var id = p.id;
                var show = (id === 'panel-auto' && type === 'auto') || (id === 'panel-aprobado' && type === 'aprobado') || (id === 'panel-rechazo' && type === 'rechazo') || (id === 'panel-radicado' && type === 'radicado');
                if (show) {
                    p.classList.remove('hidden');
                    [].forEach.call(p.querySelectorAll('input, textarea, select'), function(el) { el.disabled = false; });
                } else {
                    p.classList.add('hidden');
                    [].forEach.call(p.querySelectorAll('input, textarea, select'), function(el) { el.disabled = true; });
                }
            });
            if (panelFile) {
                panelFile.classList.toggle('hidden', type === 'rechazo' || type === 'radicado');
                var fileInput = panelFile && panelFile.querySelector('input[name="file"]');
                if (fileInput) fileInput.disabled = (type === 'rechazo' || type === 'radicado');
            }
        }
        if (tabs && tabs.length) tabs.forEach(function(t) {
            t.addEventListener('click', function() { showPanel(this.dataset.tab); });
        });
        form.addEventListener('submit', function(e) {
            if (!typeInput.value || !['rechazo','aprobado','auto','radicado'].includes(typeInput.value)) {
                e.preventDefault();
                return false;
            }
            if (typeInput.value === 'rechazo') {
                var obs = document.getElementById('rejection_observation');
                if (!obs || !obs.value.trim()) {
                    e.preventDefault();
                    alert('Debe indicar la observación (motivo del rechazo).');
                    obs && obs.focus();
                    return false;
                }
            }
            if (typeInput.value === 'radicado') {
                var rad = form.querySelector('input[name="radicado_invima"]');
                var fec = form.querySelector('input[name="fecha_radicacion"]');
                var key = form.querySelector('input[name="resolution_key"]');
                if (!rad || !rad.value.trim() || !fec || !fec.value.trim() || !key || !key.value.trim()) {
                    e.preventDefault();
                    alert('Complete número de radicado, fecha y llave/campo de registro.');
                    return false;
                }
            }
            if (typeInput.value === 'auto') {
                var due = form.querySelector('input[name="due_date"]');
                if (due && !due.value.trim()) {
                    e.preventDefault();
                    alert('Debe indicar la Fecha de vencimiento.');
                    due.focus();
                    return false;
                }
            }
        });
        window.openResponseModal = function(type) {
            if (tabsContainer) tabsContainer.style.display = 'none';
            showPanel(type);
            document.getElementById('modal-response-invima').classList.remove('hidden');
        };
        showPanel('auto');
    })();
    </script>
</div>
