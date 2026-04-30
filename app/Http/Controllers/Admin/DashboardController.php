<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Process;
use App\Models\RegulatoryEvent;

class DashboardController extends Controller
{
    public function index()
    {
        // Procesos/Solicitudes por paso del flujo (Recolección, Sometimiento, Radicado, AUTO, Finalizado)
        $processesWithSubs = Process::with(['submissions' => fn ($q) => $q->orderByDesc('id')])->get();
        $recoleccion = 0;
        $sometimiento = 0;
        $radicado = 0;
        $finalizados = 0;
        foreach ($processesWithSubs as $process) {
            $step = $process->getCurrentStep();
            match ($step) {
                Process::STEP_RECOLECCION => $recoleccion++,
                Process::STEP_SOMETIMIENTO => $sometimiento++,
                Process::STEP_RADICADO => $radicado++,
                Process::STEP_AUTO => null,
                Process::STEP_FINALIZADO => $finalizados++,
                default => null,
            };
        }
        // AUTO: cualquier sub-fase (Recolección, Sometimiento, Radicado…) mientras no esté finalizado
        $enRequerimiento = Process::whereAutoPipeline()->count();
        $totalActive = Process::where('status', '!=', Process::STATUS_FINALIZADO)->count();

        $stats = [
            'total_active' => $totalActive,
            'recoleccion' => $recoleccion,
            'sometimiento' => $sometimiento,
            'radicado' => $radicado,
            'en_requerimiento' => $enRequerimiento,
            'finalizados' => $finalizados,
            'total_companies' => Company::count(),
        ];

        // Eventos del calendario
        $events = $this->getCalendarEvents();

        return view('admin.dashboard.index', compact('stats', 'events'));
    }

    private function getCalendarEvents()
    {
        $events = [];

        // Vencimientos de AUTO (due_date) para solicitudes en cualquier estado
        $autos = RegulatoryEvent::where('event_type', RegulatoryEvent::EVENT_TYPE_AUTO)
            ->whereNotNull('due_date')
            ->with(['submission.process.client'])
            ->get();

        foreach ($autos as $auto) {
            $process = $auto->submission->process ?? null;
            if (! $process) {
                continue;
            }

            $titleBase = $process->product_reference
                ?? $process->expediente_invima
                ?? ('Solicitud '.$process->displayReference());

            $events[] = [
                'id' => 'auto-'.$auto->id,
                'title' => 'AUTO: '.\Str::limit($titleBase, 40),
                'start' => $auto->due_date->format('Y-m-d'),
                'backgroundColor' => '#f59e0b', // ámbar
                'borderColor' => '#d97706',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'auto_due',
                    'process_id' => $process->id,
                    'process_reference' => $process->displayReference(),
                    'client' => $process->client->name ?? 'N/A',
                    'auto_number' => $auto->document_number,
                    'due_date' => $auto->due_date->format('Y-m-d'),
                ],
            ];
        }

        return $events;
    }
}
