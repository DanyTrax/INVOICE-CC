<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Process;
use App\Models\Submission;
use App\Models\RegulatoryEvent;

class DashboardController extends Controller
{
    public function index()
    {
        // Procesos/Expedientes y etapas del flujo
        $totalActive = Process::where('status', '!=', Process::STATUS_FINALIZADO)->count();
        $recoleccion = Process::where('status', Process::STATUS_RECOLECCION)->count();
        $enRequerimiento = Process::where('status', Process::STATUS_EN_REQUERIMIENTO)->count();
        $finalizados = Process::where('status', Process::STATUS_FINALIZADO)->count();

        // Cargar procesos con sometimientos para separar Sometimiento (turno) vs Radicado INVIMA
        $processesWithSubs = Process::with(['submissions' => fn ($q) => $q->orderByDesc('id')])->get();
        $sometimiento = 0;
        $radicadoInvima = 0;
        foreach ($processesWithSubs as $process) {
            $lastSub = $process->submissions->first();
            if (!$lastSub) {
                continue;
            }
            if ($lastSub->status === Submission::STATUS_PENDIENTE) {
                $sometimiento++;
            } elseif ($lastSub->status === Submission::STATUS_RADICADO) {
                $radicadoInvima++;
            }
        }

        $stats = [
            'total_active' => $totalActive,
            'recoleccion' => $recoleccion,
            'sometimiento' => $sometimiento,
            'radicado' => $radicadoInvima,
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

        // Vencimientos de AUTO (due_date) para expedientes en cualquier estado
        $autos = RegulatoryEvent::where('event_type', RegulatoryEvent::EVENT_TYPE_AUTO)
            ->whereNotNull('due_date')
            ->with(['submission.process.client'])
            ->get();

        foreach ($autos as $auto) {
            $process = $auto->submission->process ?? null;
            if (!$process) {
                continue;
            }

            $titleBase = $process->product_reference
                ?? $process->expediente_invima
                ?? ('Expediente #' . $process->id);

            $events[] = [
                'id' => 'auto-' . $auto->id,
                'title' => 'AUTO: ' . \Str::limit($titleBase, 40),
                'start' => $auto->due_date->format('Y-m-d'),
                'backgroundColor' => '#f59e0b', // ámbar
                'borderColor' => '#d97706',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'auto_due',
                    'process_id' => $process->id,
                    'client' => $process->client->name ?? 'N/A',
                    'auto_number' => $auto->document_number,
                    'due_date' => $auto->due_date->format('Y-m-d'),
                ],
            ];
        }

        return $events;
    }
}
