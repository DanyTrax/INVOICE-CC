@extends('layouts.admin-flowbite')

@section('title', 'Expediente - RAMS')

@section('page-title', 'Expediente #' . $process->id . ($process->expediente_invima ? ' – ' . $process->expediente_invima : ''))

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
                    @php
                        $currentStep = $process->getCurrentStep();
                        $stepLabels = \App\Models\Process::stepLabels();
                    @endphp
                    <div class="pt-2 border-t border-gray-100">
                        <dt class="text-gray-500 mb-1">Paso actual</dt>
                        <dd class="text-xs text-gray-600 leading-relaxed">
                            @foreach($stepLabels as $num => $label)
                                @if($num > 1)<span class="text-gray-300 mx-0.5">→</span>@endif
                                @if($num === $currentStep)
                                    <span class="font-semibold text-teal-700 bg-teal-50 px-1.5 py-0.5 rounded">{{ $label }}</span>
                                @else
                                    <span class="{{ $num < $currentStep ? 'text-gray-500' : 'text-gray-400' }}">{{ $label }}</span>
                                @endif
                            @endforeach
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Expediente INVIMA</dt>
                        <dd class="font-medium text-gray-900">{{ $process->expediente_invima ?? '-' }}</dd>
                    </div>
                    @if($process->quote)
                        <div>
                            <dt class="text-gray-500">Cotización</dt>
                            <dd class="font-medium text-gray-900">
                                <a href="{{ route('admin.quotes.show', $process->quote) }}" class="text-teal-600 hover:text-teal-800 hover:underline">{{ $process->quote->consecutive }}</a>
                                ({{ $process->quote->date?->format('d/m/Y') }})
                            </dd>
                        </div>
                    @endif
                </dl>
                <form action="{{ route('admin.processes.destroy', $process) }}" method="POST" class="mt-4 pt-4 border-t border-gray-200" onsubmit="return confirm('¿Eliminar este expediente y toda su información (sometimientos, documentos, checklist)? No se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                        <i class="fas fa-trash-alt mr-2"></i> Eliminar expediente
                    </button>
                </form>
            </div>
        </div>

        <!-- Timeline vertical -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Línea de tiempo</h3>
                {{-- Indicador de paso actual: Recolección → Sometimiento → Radicado → AUTO → Finalizado --}}
                <div class="flex flex-wrap items-center gap-1 mb-6 text-xs">
                    @foreach(\App\Models\Process::stepLabels() as $num => $label)
                        @if($num > 1)<span class="text-gray-300 px-0.5">→</span>@endif
                        @if($num === $process->getCurrentStep())
                            <span class="font-semibold text-teal-700 bg-teal-100 px-2 py-1 rounded">{{ $label }}</span>
                        @else
                            <span class="text-gray-400">{{ $label }}</span>
                        @endif
                    @endforeach
                </div>

                <div class="relative">
                    <!-- Línea vertical -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    <ul class="space-y-0">
                        {{-- 1. Cotización --}}
                        @if($process->quote)
                            @php $quote = $process->quote; @endphp
                            <li class="relative pl-12 pb-8">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <a href="{{ route('admin.quotes.show', $quote) }}" class="block bg-blue-50 border border-blue-100 rounded-lg p-4 hover:bg-blue-100 transition-colors">
                                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Cotización</p>
                                    <p class="font-semibold text-gray-900">{{ $quote->consecutive }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $quote->date?->format('d/m/Y') }} · {{ $quote->status }}</p>
                                </a>
                            </li>
                        @endif

                        {{-- 2. Ciclos de trámite: un ciclo = una raíz + todos sus intentos (hijos). Nuevo ciclo solo al registrar REQUERIMIENTO AUTO desde Radicado. --}}
                        @php
                            $lastSubmission = $process->submissions->sortByDesc('id')->first();
                            $roots = $process->submissions->where('parent_id', null)->sortBy(fn($s) => $s->submission_date ?? $s->created_at);
                            // Intentos rechazados: solo se usan para permitir "Crear Nuevo Intento" en el mismo ciclo
                            // y para ofrecer la lista de vínculo en el modal de sometimiento.
                            $rejectedSubmissions = $process->submissions
                                ->filter(fn ($s) => $s->status === \App\Models\Submission::STATUS_RECHAZADO);
                            // Checklist normal vs AUTO
                            $normalItems = $process->checklistItems->where('is_for_auto', false);
                            $autoItems = $process->checklistItems->where('is_for_auto', true);
                            $allNormalApproved = $normalItems->isNotEmpty() && $normalItems->every(fn ($i) => $i->status === \App\Models\ChecklistItem::STATUS_APROBADO);
                            $allAutoApproved = $autoItems->isNotEmpty() && $autoItems->every(fn ($i) => $i->status === \App\Models\ChecklistItem::STATUS_APROBADO);
                            // Antes de AUTO se exige checklist normal; después de AUTO (En Requerimiento) se exige checklist AUTO.
                            if ($lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO) {
                                $allChecklistApproved = $allAutoApproved;
                            } else {
                                $allChecklistApproved = $allNormalApproved;
                            }
                            $processReachedEnd = $lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_APROBADO;
                            // Registrar Sometimiento:
                            //  - Caso 1: no hay sometimientos aún (primer ciclo) y checklist apropiada aprobada.
                            //  - Caso 2: último sometimiento quedó En Requerimiento (AUTO) y checklist AUTO aprobada.
                            $canRegisterSubmission = $allChecklistApproved && !$processReachedEnd && (
                                $process->submissions->isEmpty()
                                || ($lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO)
                            );
                            // Crear Nuevo Intento (mismo ciclo) solo si el último sometimiento del proceso está Rechazado.
                            $canCreateNewAttempt = $rejectedSubmissions->isNotEmpty() && $lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_RECHAZADO;
                        @endphp
                        @foreach($roots as $cycleIndex => $rootSubmission)
                            @php
                                $cycleNum = $loop->iteration;
                                // Intentos en el ciclo: raíz + todos sus descendientes (hijos, nietos, etc.) en orden cronológico.
                                $attemptsInCycle = collect();
                                $addAttempt = function ($sub) use (&$addAttempt, &$attemptsInCycle) {
                                    $attemptsInCycle->push($sub);
                                    foreach ($sub->children->sortBy(fn($c) => $c->submission_date ?? $c->created_at) as $child) {
                                        $addAttempt($child);
                                    }
                                };
                                $addAttempt($rootSubmission);
                                $lastInCycle = $attemptsInCycle->last();
                                $isClosed = in_array($lastInCycle->status, [\App\Models\Submission::STATUS_APROBADO, \App\Models\Submission::STATUS_EN_REQUERIMIENTO], true);
                                $statusBadgeClass = match($lastInCycle->status) {
                                    'Aprobado' => 'bg-green-100 text-green-800',
                                    'Rechazado' => 'bg-red-100 text-red-800',
                                    'En Requerimiento' => 'bg-yellow-100 text-yellow-800',
                                    'Radicado' => 'bg-teal-100 text-teal-800',
                                    default => 'bg-blue-100 text-blue-800',
                                };
                            @endphp
                            <li class="relative pl-12 pb-4">
                                <div class="absolute left-0 w-8 h-8 rounded-full {{ $lastInCycle->status === \App\Models\Submission::STATUS_RECHAZADO ? 'bg-red-500' : ($lastInCycle->status === \App\Models\Submission::STATUS_APROBADO ? 'bg-green-500' : ($lastInCycle->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO ? 'bg-yellow-500' : 'bg-blue-500')) }} flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <details class="group border border-gray-200 rounded-lg overflow-hidden" @if(!$isClosed) open @endif>
                                    <summary class="flex items-center gap-2 flex-wrap px-4 py-3 bg-gray-50 hover:bg-gray-100 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                        <span class="font-semibold text-gray-900">Ciclo {{ $cycleNum }}</span>
                                        <span class="text-sm text-gray-600">
                                            {{ $attemptsInCycle->count() }} intento(s)
                                            · {{ $rootSubmission->submission_date ? $rootSubmission->submission_date->format('d/m/Y') : ($rootSubmission->created_at?->format('d/m/Y') ?? '-') }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $statusBadgeClass }}">{{ $lastInCycle->status }}</span>
                                        @if($rootSubmission->quote)
                                            <a href="{{ route('admin.quotes.show', $rootSubmission->quote) }}" class="text-sm text-teal-600 hover:underline" onclick="event.stopPropagation()">Cot. {{ $rootSubmission->quote->consecutive ?? $rootSubmission->quote->id }}</a>
                                        @endif
                                        <i class="fas fa-chevron-down ml-auto text-gray-400 group-open:rotate-180 transition-transform"></i>
                                    </summary>
                                    <div class="p-4 bg-white border-t border-gray-200 space-y-6">
                                        {{-- Checklist documental (una vez por ciclo) --}}
                                        @php
                                            // Ciclo 1 usa checklist normal; Ciclo 2 (si existe) usa checklist AUTO.
                                            $itemsForThisCycle = $cycleNum === 1 ? $normalItems : $autoItems;
                                        @endphp
                                        @if($itemsForThisCycle->isNotEmpty())
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                                <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Checklist documental</p>
                                                <ul class="mt-2 space-y-1 text-sm">
                                                    @foreach($itemsForThisCycle as $item)
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
                                        @endif
                                        {{-- Intentos del ciclo: raíz + hijos (todos en el mismo ciclo) --}}
                                        @foreach($attemptsInCycle as $attemptIndex => $submission)
                                            <div class="{{ $attemptIndex > 0 ? 'mt-6 pt-4 border-t border-gray-200' : '' }}">
                                                @if($attemptsInCycle->count() > 1)
                                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Intento {{ $attemptIndex + 1 }}</p>
                                                @endif
                                                @include('admin.processes.partials.timeline-cycle-content', ['submission' => $submission, 'lastSubmission' => $lastSubmission ?? null, 'quotesForClient' => $quotesForClient ?? collect()])
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </li>
                        @endforeach

                        {{-- Sin sometimientos aún: primer ciclo (Ciclo 1) para cargar check docs y luego registrar sometimiento --}}
                        @if($roots->isEmpty())
                            <li class="relative pl-12 pb-4">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <details class="group border border-gray-200 rounded-lg overflow-hidden" open>
                                    <summary class="flex items-center gap-2 flex-wrap px-4 py-3 bg-gray-50 hover:bg-gray-100 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                        <span class="font-semibold text-gray-900">Ciclo 1</span>
                                        <span class="text-sm text-gray-600">Documentación</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">En curso</span>
                                        <i class="fas fa-chevron-down ml-auto text-gray-400 group-open:rotate-180 transition-transform"></i>
                                    </summary>
                                    <div class="p-4 bg-white border-t border-gray-200 space-y-6">
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                            <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Checklist documental</p>
                                            @if($normalItems->isNotEmpty())
                                                <ul class="mt-2 space-y-1 text-sm">
                                                    @foreach($normalItems as $item)
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
                                                <p class="text-sm text-gray-500 mt-3">Cuando todos los documentos estén en <strong>Aprobado</strong>, use el botón debajo para continuar con este ciclo.</p>
                                                @if($canRegisterSubmission)
                                                    <div class="mt-4 pt-3 border-t border-gray-200">
                                                        <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                                                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                                            <i class="fas fa-paper-plane mr-2"></i> Registrar Sometimiento
                                                        </button>
                                                    </div>
                                                @elseif($process->checklistItems->isNotEmpty())
                                                    <p class="text-sm text-amber-700 mt-3">Debe aprobar todos los documentos antes de poder registrar el sometimiento.</p>
                                                @endif
                                            @else
                                                <p class="text-sm text-gray-500 mt-2">No hay documentos. Use <strong>Gestión Documental</strong> → Agregar Documento para cargar los requisitos; luego apruebe cada uno y registre el sometimiento.</p>
                                            @endif
                                        </div>
                                        @if($process->quote_id && $process->quote)
                                            <p class="mt-3 pt-3 border-t border-gray-200 flex flex-wrap gap-2 items-center">
                                                <a href="{{ route('admin.quotes.show', $process->quote) }}" class="text-sm px-3 py-1.5 text-teal-600 hover:bg-teal-50 rounded-lg border border-teal-200 inline-flex items-center">
                                                    <i class="fas fa-file-invoice mr-1"></i> Ver cotización {{ $process->quote->consecutive }}
                                                </a>
                                            </p>
                                        @endif
                                        @if(isset($quotesForClient) && $quotesForClient->isNotEmpty())
                                        <p class="mt-3 pt-3 border-t border-gray-200 flex flex-wrap gap-2 items-center">
                                            <button type="button" onclick="typeof openLinkQuoteModalForProcess === 'function' && openLinkQuoteModalForProcess()"
                                                    class="text-sm px-3 py-1.5 text-teal-600 hover:bg-teal-50 rounded-lg border border-teal-200">
                                                <i class="fas fa-link mr-1"></i> {{ $process->quote_item_id ? 'Cambiar cotización / ítem' : 'Vincular a cotización e ítem' }}
                                            </button>
                                            <span class="text-xs text-gray-500">Este ciclo (y el expediente) quedarán vinculados al ítem elegido; en la cotización se mostrará el trámite de este expediente.</span>
                                        </p>
                                        @endif
                                    </div>
                                </details>
                            </li>
                        @endif

                        {{-- Ciclo 2: se muestra en cuanto el expediente pasa a AUTO (En Requerimiento) --}}
                        @if($roots->isNotEmpty() && $lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO)
                            <li class="relative pl-12 pb-4">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-gray-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <details class="group border border-gray-200 rounded-lg overflow-hidden" open>
                                    <summary class="flex items-center gap-2 flex-wrap px-4 py-3 bg-gray-50 hover:bg-gray-100 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                        <span class="font-semibold text-gray-900">Ciclo 2</span>
                                        <span class="text-sm text-gray-600">Checklist AUTO · Registrar sometimiento</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">En curso</span>
                                        <i class="fas fa-chevron-down ml-auto text-gray-400 group-open:rotate-180 transition-transform"></i>
                                    </summary>
                                    <div class="p-4 bg-white border-t border-gray-200 space-y-6">
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                            <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Checklist documental AUTO</p>
                                            @if($autoItems->isNotEmpty())
                                                <ul class="mt-2 space-y-1 text-sm">
                                                    @foreach($autoItems as $item)
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
                                                <p class="text-sm text-gray-600 mt-3">Cuando todos los documentos AUTO estén en <strong>Aprobado</strong>, registre el sometimiento del Ciclo 2.</p>
                                            @else
                                                <p class="text-sm text-gray-600 mt-2">Use <strong>Gestión Documental AUTO</strong> (más abajo) para agregar y aprobar los documentos; luego registre el sometimiento.</p>
                                            @endif
                                            <div class="mt-4 pt-3 border-t border-gray-200">
                                                @if($allChecklistApproved)
                                                    <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                                        <i class="fas fa-paper-plane mr-2"></i> Registrar Sometimiento
                                                    </button>
                                                @else
                                                    <p class="text-sm text-gray-700">Debe aprobar todos los documentos de Gestión Documental AUTO para poder registrar el sometimiento.</p>
                                                    <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                                                            class="mt-2 inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg cursor-not-allowed" disabled title="Aprobé todos los documentos AUTO primero">
                                                        <i class="fas fa-paper-plane mr-2"></i> Registrar Sometimiento
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            </li>
                        @endif

                        {{-- Botón Registrar Sometimiento / Crear Nuevo Intento: solo cuando NO estamos en AUTO (en AUTO ya se muestra Ciclo 2 arriba) --}}
                        @if(($canRegisterSubmission || $canCreateNewAttempt) && $roots->isNotEmpty() && (!isset($lastSubmission) || $lastSubmission->status !== \App\Models\Submission::STATUS_EN_REQUERIMIENTO))
                            <li class="relative pl-12 pb-4">
                                <div class="absolute left-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    @if($canCreateNewAttempt)
                                        <p class="text-sm text-gray-700 mb-3">El último intento fue <strong>rechazado</strong>. Puede crear otro intento en el <strong>mismo ciclo</strong> vinculándolo al intento rechazado.</p>
                                        <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                                            <i class="fas fa-redo mr-2"></i> Crear Nuevo Intento (mismo ciclo)
                                        </button>
                                    @else
                                        <p class="text-sm text-gray-700 mb-3">Registre un nuevo sometimiento para iniciar este ciclo.</p>
                                        <button type="button" onclick="document.getElementById('modal-submission').classList.remove('hidden')"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-paper-plane mr-2"></i> Registrar Sometimiento
                                        </button>
                                    @endif
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @php
        if (!isset($lastSubmission)) { $lastSubmission = $process->submissions->sortByDesc('id')->first(); }
        if (!isset($rejectedSubmissions)) {
            $rejectedSubmissions = $process->submissions
                ->filter(fn ($s) => $s->status === \App\Models\Submission::STATUS_RECHAZADO);
        }
        if (!isset($allChecklistApproved)) { $allChecklistApproved = $process->checklistItems->isNotEmpty() && $process->checklistItems->every(fn ($i) => $i->status === \App\Models\ChecklistItem::STATUS_APROBADO); }
        if (!isset($canRegisterSubmission)) {
            $processReachedEnd = $lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_APROBADO;
            $canRegisterSubmission = $allChecklistApproved && !$processReachedEnd && (
                $process->submissions->isEmpty()
                || ($lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO)
            );
        }
        if (!isset($canCreateNewAttempt)) {
            $canCreateNewAttempt = $rejectedSubmissions->isNotEmpty() && $lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_RECHAZADO;
        }
    @endphp

    {{-- 2. Gestión Documental --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-folder-open text-teal-600 mr-2"></i> Gestión Documental
            </h3>
            <button type="button"
                    onclick="document.getElementById('add-doc-is-for-auto').value='0';document.getElementById('modal-add-document').classList.remove('hidden')"
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
                    @forelse($normalItems as $item)
                        @php
                            $badgeClass = match($item->status) {
                                'Aprobado' => 'bg-green-100 text-green-800',
                                'Recibido' => 'bg-blue-100 text-blue-800',
                                'Traducción' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium text-gray-900">
                                <i class="fas {{ $item->status === 'Aprobado' ? 'fa-check-circle text-green-600' : 'fa-circle text-gray-400' }} mr-2"></i>
                                {{ $item->document_name }}
                            </td>
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

    {{-- 2.b Gestión Documental AUTO
         - Visible mientras haya documentos AUTO en el expediente
         - O cuando el último sometimiento está En Requerimiento (AUTO)
    --}}
    @if(($autoItems ?? collect())->isNotEmpty() || (isset($lastSubmission) && $lastSubmission && $lastSubmission->status === \App\Models\Submission::STATUS_EN_REQUERIMIENTO))
    <div class="bg-white rounded-lg shadow-sm border border-amber-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-folder-open text-amber-600 mr-2"></i> Gestión Documental AUTO
            </h3>
            <button type="button"
                    onclick="document.getElementById('add-doc-is-for-auto').value='1';document.getElementById('modal-add-document').classList.remove('hidden')"
                    class="inline-flex items-center px-3 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700">
                <i class="fas fa-plus mr-2"></i> Agregar Documento AUTO
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
                    @forelse($autoItems as $item)
                        @php
                            $badgeClass = match($item->status) {
                                'Aprobado' => 'bg-green-100 text-green-800',
                                'Recibido' => 'bg-blue-100 text-blue-800',
                                'Traducción' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium text-gray-900">
                                <i class="fas {{ $item->status === 'Aprobado' ? 'fa-check-circle text-green-600' : 'fa-circle text-gray-400' }} mr-2"></i>
                                {{ $item->document_name }}
                            </td>
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
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">No hay documentos AUTO. Use &quot;Agregar Documento AUTO&quot; para registrar requisitos de un requerimiento AUTO.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

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
        if (!isset($rejectedSubmissions)) { $rejectedSubmissions = $process->submissions->where('status', \App\Models\Submission::STATUS_RECHAZADO); }
        if (!isset($allChecklistApproved)) { $allChecklistApproved = $process->checklistItems->isNotEmpty() && $process->checklistItems->every(fn ($i) => $i->status === \App\Models\ChecklistItem::STATUS_APROBADO); }
        $latestAutoDue = $process->submissions->flatMap->regulatoryEvents
            ->where('event_type', \App\Models\RegulatoryEvent::EVENT_TYPE_AUTO)
            ->whereNotNull('due_date')
            ->max('due_date');
        // Días hasta el vencimiento (positivo = falta X días, 0 = hoy, negativo = ya venció).
        $daysLeftRaw = $latestAutoDue
            ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($latestAutoDue)->startOfDay(), false)
            : null;
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
                    <input type="hidden" name="is_for_auto" id="add-doc-is-for-auto" value="0">
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

    {{-- Modal: Vincular ciclo a cotización e ítem --}}
    @if(isset($quotesForClient) && $quotesForClient->isNotEmpty())
    <div id="modal-link-quote" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('modal-link-quote').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Vincular ciclo a cotización</h4>
                <p class="text-sm text-gray-600 mb-4">Seleccione la cotización y luego el ítem (servicio) al que vincula este ciclo.</p>
                <form id="form-link-quote" method="post" action="">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="link-quote-quote_id" class="block text-sm font-medium text-gray-700">Cotización <span class="text-red-500">*</span></label>
                            <select name="quote_id" id="link-quote-quote_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="">— Buscar / seleccionar cotización —</option>
                                @foreach($quotesForClient as $q)
                                    <option value="{{ $q->id }}">{{ $q->consecutive }} · {{ $q->date?->format('d/m/Y') ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="link-quote-quote_item_id" class="block text-sm font-medium text-gray-700">Ítem de la cotización <span class="text-red-500">*</span></label>
                            <select name="quote_item_id" id="link-quote-quote_item_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="">— Primero seleccione una cotización —</option>
                                @foreach($quotesForClient as $q)
                                    @foreach($q->quoteItems as $qi)
                                        <option value="{{ $qi->id }}" data-quote="{{ $q->id }}">{{ $q->consecutive }} · #{{ $qi->item_position }} · {{ $qi->serviceType->name ?? 'Servicio' }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-link-quote').classList.add('hidden')"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">Vincular</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
    var linkQuoteBaseUrl = '{{ url("admin/submissions") }}';
    var processLinkQuoteUrl = '{{ route("admin.processes.link-to-quote", $process) }}';
    function openLinkQuoteModal(submissionId) {
        var form = document.getElementById('form-link-quote');
        if (!form) return;
        form.action = linkQuoteBaseUrl + '/' + submissionId + '/link-quote';
        var methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.value = 'PUT';
        var quoteSelect = document.getElementById('link-quote-quote_id');
        var itemSelect = document.getElementById('link-quote-quote_item_id');
        if (quoteSelect) quoteSelect.value = '';
        if (itemSelect) {
            itemSelect.value = '';
            var opts = itemSelect.querySelectorAll('option[data-quote]');
            opts.forEach(function(opt) { opt.style.display = 'none'; });
        }
        document.getElementById('modal-link-quote').classList.remove('hidden');
    }
    function openLinkQuoteModalForProcess() {
        var form = document.getElementById('form-link-quote');
        if (!form) return;
        form.action = processLinkQuoteUrl;
        var methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.value = 'POST';
        var quoteSelect = document.getElementById('link-quote-quote_id');
        var itemSelect = document.getElementById('link-quote-quote_item_id');
        if (quoteSelect) quoteSelect.value = '';
        if (itemSelect) {
            itemSelect.value = '';
            var opts = itemSelect.querySelectorAll('option[data-quote]');
            opts.forEach(function(opt) { opt.style.display = 'none'; });
        }
        document.getElementById('modal-link-quote').classList.remove('hidden');
    }
    document.getElementById('link-quote-quote_id') && document.getElementById('link-quote-quote_id').addEventListener('change', function() {
        var quoteId = this.value;
        var itemSelect = document.getElementById('link-quote-quote_item_id');
        if (!itemSelect) return;
        itemSelect.value = '';
        var opts = itemSelect.querySelectorAll('option[data-quote]');
        opts.forEach(function(opt) {
            opt.style.display = opt.getAttribute('data-quote') === quoteId ? '' : 'none';
        });
    });
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
    // Paso intermedio tras REQUERIMIENTO AUTO: advertencia y botón Aceptar antes de permitir "Registrar Sometimiento".
    (function() {
        var acceptBtn = document.getElementById('btn-auto-accept-checklist');
        if (!acceptBtn) return;
        acceptBtn.addEventListener('click', function() {
            var warning = document.getElementById('auto-checklist-warning');
            var registerBtn = document.getElementById('btn-register-submission-after-auto');
            if (warning) warning.classList.add('hidden');
            acceptBtn.classList.add('hidden');
            if (registerBtn) registerBtn.classList.remove('hidden');
        });
    })();
    </script>

    @include('admin.processes.partials.modal-submission', [
        'process' => $process,
        'rejectedSubmissions' => $rejectedSubmissions,
        'quotesForClient' => $quotesForClient ?? collect(),
        'canCreateNewAttempt' => $canCreateNewAttempt ?? false,
    ])
    @if($lastSubmission)
        @include('admin.processes.partials.modal-response-invima', ['submission' => $lastSubmission])
    @endif
@endsection
