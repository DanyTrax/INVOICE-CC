<div id="modal-submission" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('modal-submission').classList.add('hidden')"></div>
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.processes.submissions.store', $process) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-paper-plane text-blue-500 mr-2"></i> Registrar Sometimiento
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Todos los ítems de la checklist deben estar en estado <strong>Aprobado</strong>. El expediente pasará a <strong>Radicado</strong>.</p>
                    <div class="space-y-4">
                        <div>
                            <label for="submission_radicado_invima" class="block text-sm font-medium text-gray-700">Radicado INVIMA</label>
                            <input type="text" name="radicado_invima" id="submission_radicado_invima" value="{{ old('radicado_invima') }}" maxlength="64"
                                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('radicado_invima')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="submission_tracking_id" class="block text-sm font-medium text-gray-700">Tracking ID</label>
                            <input type="text" name="tracking_id" id="submission_tracking_id" value="{{ old('tracking_id') }}" maxlength="64"
                                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('tracking_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="submission_fecha_radicacion" class="block text-sm font-medium text-gray-700">Fecha de radicación</label>
                            <input type="date" name="fecha_radicacion" id="submission_fecha_radicacion" value="{{ old('fecha_radicacion', now()->format('Y-m-d')) }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('fecha_radicacion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="submission_status" class="block text-sm font-medium text-gray-700">Estado <span class="text-red-500">*</span></label>
                            <select name="status" id="submission_status" required
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                @foreach(\App\Models\Submission::statuses() as $s)
                                    <option value="{{ $s }}" {{ old('status', \App\Models\Submission::STATUS_EN_ESTUDIO) === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        @if($rejectedSubmissions->isNotEmpty())
                            <div>
                                <label for="submission_parent_id" class="block text-sm font-medium text-gray-700">Vincular a rechazo anterior (opcional)</label>
                                <select name="parent_id" id="submission_parent_id"
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="">— Nuevo sometimiento (sin rechazo previo) —</option>
                                    @foreach($rejectedSubmissions as $rej)
                                        <option value="{{ $rej->id }}" {{ (string) old('parent_id') === (string) $rej->id ? 'selected' : '' }}>
                                            {{ $rej->radicado_invima ?? 'ID ' . $rej->id }} · {{ $rej->fecha_radicacion?->format('d/m/Y') ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Guardar Sometimiento
                    </button>
                    <button type="button" onclick="document.getElementById('modal-submission').classList.add('hidden')"
                            class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
