<div id="modal-auto" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('modal-auto').classList.add('hidden')"></div>
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.submissions.events.store-auto', $submission) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-gavel text-yellow-500 mr-2"></i> Registrar Auto
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Sometimiento: {{ $submission->radicado_invima ?? $submission->id }}. El expediente pasará a <strong>En Requerimiento</strong> y se calculará la fecha límite (90 días hábiles).</p>
                    <div class="space-y-4">
                        <div>
                            <label for="auto_document_number" class="block text-sm font-medium text-gray-700">Número del Auto</label>
                            <input type="text" name="document_number" id="auto_document_number" value="{{ old('document_number') }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                        </div>
                        <div>
                            <label for="auto_event_date" class="block text-sm font-medium text-gray-700">Fecha del documento</label>
                            <input type="date" name="event_date" id="auto_event_date" value="{{ old('event_date') }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                        </div>
                        <div>
                            <label for="auto_notification_date" class="block text-sm font-medium text-gray-700">Fecha de notificación <span class="text-red-500">*</span></label>
                            <input type="date" name="notification_date" id="auto_notification_date" value="{{ old('notification_date', now()->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                        </div>
                        <div>
                            <label for="auto_file" class="block text-sm font-medium text-gray-700">PDF (opcional)</label>
                            <input type="file" name="file" id="auto_file" accept=".pdf"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Guardar Auto
                    </button>
                    <button type="button" onclick="document.getElementById('modal-auto').classList.add('hidden')"
                            class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
