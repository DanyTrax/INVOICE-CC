<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas
        $stats = [
            'active_registrations' => Registration::count(),
            'expiring_this_month' => Registration::whereMonth('expiration_date', now()->month)
                ->whereYear('expiration_date', now()->year)
                ->count(),
            'in_process_invima' => Registration::where('status', 'en_tramite')->count(),
            'total_companies' => Company::count(),
        ];

        // Eventos del calendario
        $events = $this->getCalendarEvents();

        return view('admin.dashboard.index', compact('stats', 'events'));
    }

    private function getCalendarEvents()
    {
        $events = [];

        // Vencimientos (rojo)
        $expirations = Registration::whereNotNull('expiration_date')
            ->select('id', 'product_name', 'expiration_date', 'company_id')
            ->with('company:id,name')
            ->get();

        foreach ($expirations as $reg) {
            $events[] = [
                'id' => 'exp-' . $reg->id,
                'title' => 'Vence: ' . \Str::limit($reg->product_name, 30),
                'start' => $reg->expiration_date->format('Y-m-d'),
                'backgroundColor' => '#ef4444',
                'borderColor' => '#dc2626',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'expiration',
                    'registration_id' => $reg->id,
                    'company' => $reg->company->name ?? 'N/A',
                ],
            ];
        }

        // Límites de respuesta (azul)
        $responseLimits = Registration::whereNotNull('response_limit_date')
            ->select('id', 'product_name', 'response_limit_date', 'company_id')
            ->with('company:id,name')
            ->get();

        foreach ($responseLimits as $reg) {
            $events[] = [
                'id' => 'resp-' . $reg->id,
                'title' => 'Radicar: ' . \Str::limit($reg->product_name, 30),
                'start' => $reg->response_limit_date->format('Y-m-d'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#2563eb',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'response_limit',
                    'registration_id' => $reg->id,
                    'company' => $reg->company->name ?? 'N/A',
                ],
            ];
        }

        return $events;
    }
}
