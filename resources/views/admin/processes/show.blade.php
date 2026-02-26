@extends('layouts.admin-flowbite')

@section('title', 'Expediente - RAMS')

@section('page-title', 'Expediente ' . ($process->expediente_invima ?? 'N/A'))

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <a href="{{ route('admin.processes.index') }}" class="text-sm font-medium text-gray-700 hover:text-teal-700">Expedientes</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
            <span class="text-sm font-medium text-gray-500">Ver</span>
        </div>
    </li>
@endsection

@section('content')
    {{-- 1. Resumen y Línea de tiempo --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Resumen del expediente -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Cliente</dt>
                        <dd class="font-medium text-gray-900">{{ $process->client->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tipo de servicio</dt>
                        <dd class="font-medium text-gray-900">{{ $process->quoteItem?->serviceType?->name ?? $process->serviceType?->name ?? '-' }}</dd>
                    </div>
                    @if($process->product_reference)
                    <div>
                        <dt class="text-gray-500">Producto / Referencia</dt>
                        <dd class="font-medium text-gray-900">{{ $process->product_reference }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500">Nombre de correo</dt>
                        <dd class="font-medium text-gray-900">{{ $process->email_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Estado</dt>
                        <dd>
                            @php
                                $statusStyles = [
                                    'Recolección' => 'bg-gray-100 text-gray-800',
                                    'Radicado' => 'bg-blue-100 text-blue-800',
                                    'En Requerimiento' => 'bg-yellow-100 text-yellow-800',
                                    'Finalizado' => 'bg-green-100 text-green-800',
                                ];
                                $style = $statusStyles[$process->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $style }}">{{ $process->status }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Expediente INVIMA</dt>
                        <dd class="font-medium text-gray-900">{{ $process->expediente_invima ?? '-' }}</dd>
                    </div>
                    @if($process->quoteItem->quote ?? null)
                        <div>
                            <dt class="text-gray-500">Cotización</dt>
                            <dd class="font-medium text-gray-900">{{ $process->quoteItem->quote->consecutive ?? '-' }} ({{ $process->quoteItem->quote->date?->format('d/m/Y') }})</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Timeline vertical -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Línea de tiempo</h3>

                <div class="relative">
                    <!-- Línea vertical -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    <ul class="space-y-0">
                        {{-- 1. Cotización --}}
                        @if($process->quoteItem?->quote)
                            @php $quote = $process->quoteItem->quote; @endphp
                            <li class="relative pl-12 pb-8">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Cotización</p>
                                    <p class="font-semibold text-gray-900">{{ $quote->consecutive }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $quote->date->format('d/m/Y') }} · {{ $quote->status }}</p>
                                </div>
                            </li>
                        @endif

                        {{-- 2. Checklist documental --}}
                        @if($process->checklistItems->isNotEmpty())
                            <li class="relative pl-12 pb-8">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-gray-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Checklist documental</p>
                                    <ul class="mt-2 space-y-1 text-sm">
                                        @foreach($process->checklistItems as $item)
                                            @php
                                                $itemStyle = match($item->status) {
                                                    'Aprobado' => 'text-green-700',
                                                    'Traducción' => 'text-yellow-700',
                                                    'Recibido' => 'text-blue-700',
                                                    default => 'text-gray-700',
                                                };
                                            @endphp
                                            <li class="flex items-center gap-2 {{ $itemStyle }}">
                                                <i class="fas fa-{{ $item->status === 'Aprobado' ? 'check-circle' : 'circle' }} text-xs"></i>
                                                {{ $item->document_name }}
                                                <span class="text-xs">({{ $item->status }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- 3. Sometimientos y eventos (raíz primero, ordenados por fecha) --}}
                        @php
                            $lastSubmission = $process->submissions->sortByDesc('id')->first();
                            $rootSubmissions = $process->submissions->where('parent_id', null)->sortBy(fn($s) => $s->submission_date ?? $s->created_at);
                        @endphp
                        @foreach($rootSubmissions as $submission)
                            @include('admin.processes.partials.timeline-submission', ['submission' => $submission, 'attemptNum' => $loop->iteration, 'lastSubmission' => $lastSubmission ?? null])
                        @endforeach

                        @if($rootSubmissions->isEmpty() && $process->checklistItems->isEmpty() && !$process->quoteItem?->quote)
                            <li class="relative pl-12 pb-4 text-sm text-gray-500">
                                Sin eventos aún en la línea de tiempo.
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Gestión Documental --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-folder-open text-teal-600 mr-2"></i> Gestión Documental
            </h3>
            <button type="button" onclick="document.getElementById('modal-add-document').classList.remove('hidden')"
                    class="inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700">
                <i class="fas fa-plus mr-2"></i> Agregar Documento
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-3 py-2">Documento</th>
                        <th class="px-3 py-2 w-32">Estado</th>
                        <th class="px-3 py-2">Observación</th>
                        <th class="px-3 py-2 w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($process->checklistItems as $item)
                        @php
                            $badgeClass = match($item->status) {
                                'Aprobado' => 'bg-green-100 text-green-800',
                                'Recibido' => 'bg-blue-100 text-blue-800',
                                'Traducción' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $item->document_name }}</td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeClass }}">{{ $item->status }}</span>
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ Str::limit($item->observation_agent ?? '-', 50) }}</td>
                            <td class="px-3 py-2">
                                <button type="button" onclick="openChecklistModal({{ $item->id }}, '{{ addslashes($item->document_name) }}', '{{ $item->status }}', '{{ addslashes($item->observation_agent ?? '') }}')"
                                        class="text-teal-600 hover:text-teal-800 text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i> Cambiar estado
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">No hay documentos en la checklist. Use &quot;+ Agregar Documento&quot; para agregar requisitos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Documentos en Drive --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-cloud-upload-alt text-teal-600 mr-2"></i> Documentos en Drive
        </h3>
        @if($process->drive_folder_id)
            <p class="text-sm text-gray-600 mb-3">
                <a href="{{ $process->drive_folder_url ?? 'https://drive.google.com/drive/folders/' . $process->drive_folder_id }}" target="_blank" rel="noopener"
                   class="inline-flex items-center text-teal-600 hover:text-teal-800 font-medium">
                    <i class="fas fa-external-link-alt mr-2"></i> Abrir carpeta en Google Drive
                </a>
            </p>
        @else
            <p class="text-sm text-gray-500 mb-3">La carpeta en Drive se creará al subir el primer documento (si está configurado Google Drive en Configuración).</p>
        @endif
        <form action="{{ route('admin.processes.documents.upload', $process) }}" method="POST" enctype="multipart/form-data" class="mb-4 flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label for="process-document-file" class="block text-sm font-medium text-gray-700 mb-1">Subir documento</label>
                <input type="file" name="document" id="process-document-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" required
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700">
                <i class="fas fa-upload mr-2"></i> Subir
            </button>
        </form>
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-3 py-2">Documento</th>
                        <th class="px-3 py-2 w-28">Subido</th>
                        <th class="px-3 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($process->processDocuments as $doc)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $doc->file_name }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap gap-2">
                                    @if($doc->drive_id)
                                        <a href="{{ route('admin.processes.documents.view', [$process, $doc]) }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 bg-teal-600 text-white text-xs font-medium rounded hover:bg-teal-700">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <a href="{{ route('admin.processes.documents.download', [$process, $doc]) }}" class="inline-flex items-center px-2.5 py-1.5 bg-teal-600 text-white text-xs font-medium rounded hover:bg-teal-700">
                                            <i class="fas fa-download mr-1"></i> Descargar
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.processes.documents.destroy', [$process, $doc]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este documento? Se borrará también en Google Drive.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                                            <i class="fas fa-trash mr-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-6 text-center text-gray-500">Aún no hay documentos subidos. Use el formulario de arriba para subir archivos a la carpeta de Drive.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Alertas: semáforo En Requerimiento (días restantes) --}}
    @php
        if (!isset($lastSubmission)) { $lastSubmission = $process->submissions->sortByDesc('id')->first(); }
        $rejectedSubmissions = $process->submissions->where('status', \App\Models\Submission::STATUS_RECHAZADO);
        $allChecklistApproved = $process->checklistItems->isNotEmpty() && $process->checklistItems->every(fn ($i) => $i->status === \App\Models\ChecklistItem::STATUS_APROBADO);
        $latestAutoDue = $process->submissions->flatMap->regulatoryEvents->where('event_type', \App\Models\RegulatoryEvent::EVENT_TYPE_AUTO)->whereNotNull('due_date')->max('due_date');
        $daysLeftRaw = $latestAutoDue ? \Carbon\Carbon::parse($latestAutoDue)->startOfDay()->diffInDays(now()->startOfDay(), false) : null;
        $daysLeft = $daysLeftRaw !== null ? (int) $daysLeftRaw : null;
    @endphp
    @if($process->status === 'En Requerimiento' && $daysLeft !== null)
        <div class="mt-6 p-4 rounded-lg border-2 {{ $daysLeft <= 30 ? 'bg-red-50 border-red-300' : 'bg-amber-50 border-amber-200' }}">
            @if($daysLeft < 0)
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-red-100 text-red-800 animate-pulse">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Vencido hace {{ abs($daysLeft) }} día(s)
                </span>
            @elseif($daysLeft > 30)
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                    <i class="fas fa-clock mr-2"></i> Vence en {{ $daysLeft }} días
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-red-100 text-red-800 animate-pulse">
                    <i class="fas fa-exclamation-triangle mr-2"></i> ¡ACCIÓN INMEDIATA! Vence en {{ $daysLeft }} días
                </span>
            @endif
        </div>
    @endif

    {{-- Acciones: Sometimiento y Nuevo Intento (respuesta INVIMA se hace con Aprobar/Rechazar en la línea de tiempo) --}}
    @php
        $hasPendingSubmission = $process->submissions->contains('status', \App\Models\Submission::STATUS_PENDIENTE);
        $processReachedEnd = $lastSubmission && in_array($lastSubmission->status, [\App\Models\Submission::STATUS_APROBADO, \App\Models\Submission::STATUS_EN_REQUERIMIENTO]);
        $canRegisterSubmission = $allChecklistApproved && !$hasPendingSubmission && !$processReachedEnd;
        $canCreateNewAttempt = $rejectedSubmissions->isNotEmpty() && $lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_RECHAZADO;
        $submitDisabledTitle = !$allChecklistApproved ? 'Debe aprobar todos los documentos antes de radicar.' : ($hasPendingSubmission ? 'Hay un sometimiento pendiente; use Aprobar o Rechazar en la línea de tiempo.' : ($processReachedEnd ? 'El proceso llegó a resolución aprobatoria o auto; está deshabilitado. Elimine en la línea de tiempo para reanudar.' : ''));
    @endphp
    <div class="mt-6 flex flex-wrap gap-3 items-center">
        @if($canRegisterSubmission)
            <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-paper-plane mr-2"></i> Registrar Sometimiento
            </button>
        @else
            <span class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed" title="{{ $submitDisabledTitle }}">
                <i class="fas fa-paper-plane mr-2"></i> Registrar Sometimiento
            </span>
        @endif
        @if($canCreateNewAttempt)
            <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <i class="fas fa-redo mr-2"></i> Crear Nuevo Intento
            </button>
        @endif
        @if(!$allChecklistApproved)
            <p class="text-sm text-amber-700 w-full">Debe aprobar todos los documentos antes de radicar.</p>
        @endif
        @if($processReachedEnd)
            <p class="text-sm text-gray-600 w-full">Proceso en estado final (resolución o auto). Para reanudar acciones, elimine el intento correspondiente en la línea de tiempo.</p>
        @endif
    </div>

    {{-- Modal: Cambiar estado de documento --}}
    <div id="modal-checklist-item" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('modal-checklist-item').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Cambiar estado</h4>
                <p id="modal-checklist-doc-name" class="text-sm text-gray-600 mb-4"></p>
                <form id="form-checklist-update" method="post" action="">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="Pendiente">Pendiente</option>
                            <option value="Recibido">Recibido</option>
                            <option value="Traducción">Traducción</option>
                            <option value="Aprobado">Aprobado</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observación (feedback al cliente)</label>
                        <textarea name="observation_agent" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Opcional"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-checklist-item').classList.add('hidden')" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Agregar documento --}}
    <div id="modal-add-document" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('modal-add-document').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Agregar Documento</h4>
                <form action="{{ route('admin.processes.checklist-items.store', $process) }}" method="post">
                    @csrf
                    <div class="mb-4">
                        <label for="document_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del documento / requisito</label>
                        <input type="text" name="document_name" id="document_name" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej: Certificado de Buenas Prácticas">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-add-document').classList.add('hidden')" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Editar intento (sometimiento) --}}
    <div id="modal-edit-submission" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('modal-edit-submission').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-edit text-teal-600 mr-2"></i> Editar intento</h4>
                <form id="form-edit-submission" method="post" action="">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de sometimiento</label>
                            <input type="datetime-local" name="submission_date" id="edit_submission_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID de sometimiento</label>
                            <input type="text" name="submission_code" id="edit_submission_code" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Radicado</label>
                            <input type="text" name="radicado_invima" id="edit_radicado_invima" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Llave / Seguimiento</label>
                            <input type="text" name="tracking_id" id="edit_tracking_id" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de radicación</label>
                            <input type="date" name="fecha_radicacion" id="edit_fecha_radicacion" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="status" id="edit_status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                @foreach(\App\Models\Submission::statuses() as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observación rechazo</label>
                            <textarea name="rejection_observation" id="edit_rejection_observation" rows="2" maxlength="2000" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-edit-submission').classList.add('hidden')" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Editar evento (Auto / Resolución) --}}
    <div id="modal-edit-event" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('modal-edit-event').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-edit text-teal-600 mr-2"></i> Editar evento</h4>
                <form id="form-edit-event" method="post" action="">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número / Documento</label>
                            <input type="text" name="document_number" id="edit_event_document_number" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div id="edit-event-field-notification" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de notificación (Auto)</label>
                            <input type="date" name="notification_date" id="edit_event_notification_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div id="edit-event-field-event-date" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de resolución</label>
                            <input type="date" name="event_date" id="edit_event_event_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div id="edit-event-field-resolution-key" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Llave</label>
                            <input type="text" name="resolution_key" id="edit_event_resolution_key" maxlength="64" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-edit-event').classList.add('hidden')" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openChecklistModal(id, docName, currentStatus, observation) {
        var baseUrl = '{{ url('admin/checklist-items') }}';
        document.getElementById('form-checklist-update').action = baseUrl + '/' + id;
        document.getElementById('modal-checklist-doc-name').textContent = docName;
        document.querySelector('#form-checklist-update select[name="status"]').value = currentStatus || 'Pendiente';
        document.querySelector('#form-checklist-update textarea[name="observation_agent"]').value = observation || '';
        document.getElementById('modal-checklist-item').classList.remove('hidden');
    }
    document.querySelectorAll('.js-edit-submission').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = document.getElementById('form-edit-submission');
            form.action = this.dataset.url || '';
            var fields = ['submission_date', 'submission_code', 'radicado_invima', 'tracking_id', 'fecha_radicacion', 'status', 'rejection_observation'];
            fields.forEach(function(name) {
                var camel = name.replace(/_([a-z])/g, function(_, l) { return l.toUpperCase(); });
                var val = this.dataset[camel] || '';
                var el = form.querySelector('[name="' + name + '"]');
                if (el) el.value = val;
            }.bind(this));
            document.getElementById('modal-edit-submission').classList.remove('hidden');
        });
    });
    document.querySelectorAll('.js-edit-event').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = document.getElementById('form-edit-event');
            form.action = this.dataset.url || '';
            document.getElementById('edit_event_document_number').value = this.dataset.documentNumber || '';
            document.getElementById('edit_event_notification_date').value = this.dataset.notificationDate || '';
            document.getElementById('edit_event_event_date').value = this.dataset.eventDate || '';
            document.getElementById('edit_event_resolution_key').value = this.dataset.resolutionKey || '';
            var type = (this.dataset.eventType || '').toUpperCase();
            document.getElementById('edit-event-field-notification').classList.toggle('hidden', type !== 'AUTO');
            document.getElementById('edit-event-field-event-date').classList.toggle('hidden', type !== 'RESOLUCION');
            document.getElementById('edit-event-field-resolution-key').classList.toggle('hidden', type !== 'RESOLUCION');
            document.getElementById('modal-edit-event').classList.remove('hidden');
        });
    });
    </script>

    @include('admin.processes.partials.modal-submission', ['process' => $process, 'rejectedSubmissions' => $rejectedSubmissions])
    @if($lastSubmission)
        @include('admin.processes.partials.modal-response-invima', ['submission' => $lastSubmission])
    @endif
@endsection
