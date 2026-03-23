{{-- Solo HTML del modal. El JS va en process-assignment-modal-js.blade.php e inclúyelo con @include dentro de @push('scripts') en monitor/history. --}}
@once
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
                <p class="text-sm text-gray-600 mb-3">Seleccione usuarios que podrán ver este expediente. Si marca <strong>Línea de tiempo</strong>, podrá alimentar la línea de tiempo, subir a Drive, gestión documental (normal y AUTO) y cambiar estados de checklist, según los permisos del rol.</p>
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
@endonce
