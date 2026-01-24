<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Document;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ClientPortalController extends Controller
{
    /**
     * Registros (expedientes) a los que el cliente tiene acceso (sus empresas).
     */
    protected function registrationsQuery(Request $request)
    {
        $companyIds = auth()->user()->companies()->pluck('companies.id');
        $query = Registration::whereIn('company_id', $companyIds)
            ->with(['company', 'assignedSpecialist', 'documents']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                  ->orWhere('registration_number', 'like', "%{$s}%")
                  ->orWhere('quotation_number', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('updated_at', 'desc');
    }

    protected function ensureClientCanAccess(Registration $registration): void
    {
        $companyIds = auth()->user()->companies()->pluck('companies.id');
        if (!$companyIds->contains($registration->company_id)) {
            abort(404);
        }
    }

    public function dashboard(Request $request)
    {
        // Obtener IDs de empresas del cliente autenticado
        $companyIds = auth()->user()->companies()->pluck('companies.id');
        
        // Si el cliente no tiene empresas asignadas, no mostrar nada
        if ($companyIds->isEmpty()) {
            $vigentes = 0;
            $enTramite = 0;
            $requerimiento = 0;
            $vencidos = 0;
            $proximosVencer = 0;
            $registrations = collect();
            $calendarRegistrations = collect();
            $calendarEvents = [];
            
            return view('portal.dashboard', compact(
                'vigentes', 'enTramite', 'requerimiento', 'vencidos', 'proximosVencer',
                'registrations', 'calendarRegistrations', 'calendarEvents'
            ));
        }
        
        // Query base filtrado por empresas del cliente
        $base = Registration::whereIn('company_id', $companyIds);

        $vigentes = (clone $base)->where('status', 'vigente')->count();
        $enTramite = (clone $base)->where('status', 'tramite')->count();
        $requerimiento = (clone $base)->where('status', 'requerimiento')->count();
        $vencidos = (clone $base)->where('status', 'vencido')->count();

        $proximosVencer = (clone $base)
            ->where('status', 'vigente')
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '>=', now())
            ->where('expiration_date', '<=', now()->addDays(30))
            ->count();

        $registrations = $this->registrationsQuery($request)->limit(5)->get();

        $calendarRegistrations = (clone $base)
            ->where(function ($q) {
                $q->whereNotNull('expiration_date')
                  ->orWhereNotNull('response_limit_date');
            })
            ->orderByRaw('COALESCE(expiration_date, response_limit_date) ASC')
            ->get();

        // Eventos para FullCalendar (formato JSON) - solo del cliente
        $calendarEvents = $this->getCalendarEvents($base, $companyIds);

        return view('portal.dashboard', compact(
            'vigentes', 'enTramite', 'requerimiento', 'vencidos', 'proximosVencer',
            'registrations', 'calendarRegistrations', 'calendarEvents'
        ));
    }

    public function index(Request $request)
    {
        $registrations = $this->registrationsQuery($request)->paginate(15);
        return view('portal.registrations.index', compact('registrations'));
    }

    public function show(Registration $registration)
    {
        $this->ensureClientCanAccess($registration);
        $registration->load(['company', 'assignedSpecialist', 'documents']);
        $driveService = app(GoogleDriveService::class);
        $validDocs = collect();
        foreach ($registration->documents as $doc) {
            if ($doc->drive_id) {
                try {
                    $driveService->getFileInfo($doc->drive_id);
                    $validDocs->push($doc);
                } catch (\Throwable $e) {
                    Log::debug('Portal: doc no disponible en Drive', ['id' => $doc->id]);
                }
            } elseif ($doc->file_path && str_starts_with($doc->file_path, 'registration-documents/') && Storage::disk('local')->exists($doc->file_path)) {
                $validDocs->push($doc);
            }
        }
        $registration->setRelation('documents', $validDocs);

        return view('portal.registrations.show', compact('registration'));
    }

    public function viewDocument(Registration $registration, Document $document)
    {
        $this->ensureClientCanAccess($registration);
        if ($document->registration_id !== $registration->id) {
            abort(404);
        }
        return app(\App\Http\Controllers\Admin\RegistrationController::class)
            ->viewDocument($registration, $document);
    }

    public function downloadDocument(Registration $registration, Document $document)
    {
        $this->ensureClientCanAccess($registration);
        if ($document->registration_id !== $registration->id) {
            abort(404);
        }
        return app(\App\Http\Controllers\Admin\RegistrationController::class)
            ->downloadDocument($registration, $document);
    }

    /**
     * Generar eventos para FullCalendar (solo expedientes del cliente).
     */
    protected function getCalendarEvents($baseQuery, $companyIds)
    {
        $events = [];

        // Asegurar que el query base esté filtrado por las empresas del cliente
        // (por si acaso el clone no mantiene el filtro)
        $baseQuery = Registration::whereIn('company_id', $companyIds);

        // Vencimientos (rojo)
        $expirations = (clone $baseQuery)
            ->whereNotNull('expiration_date')
            ->select('id', 'product_name', 'expiration_date', 'company_id')
            ->get();

        foreach ($expirations as $reg) {
            // Verificación adicional: asegurar que pertenece a una empresa del cliente
            if (!$companyIds->contains($reg->company_id)) {
                continue;
            }
            
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
                ],
            ];
        }

        // Requerimientos (ámbar) - cuando hay response_limit_date y status es requerimiento
        $requerimientos = (clone $baseQuery)
            ->where('status', 'requerimiento')
            ->whereNotNull('response_limit_date')
            ->select('id', 'product_name', 'response_limit_date', 'company_id')
            ->get();

        foreach ($requerimientos as $reg) {
            // Verificación adicional: asegurar que pertenece a una empresa del cliente
            if (!$companyIds->contains($reg->company_id)) {
                continue;
            }
            
            $events[] = [
                'id' => 'req-' . $reg->id,
                'title' => 'Requerimiento: ' . \Str::limit($reg->product_name, 25),
                'start' => $reg->response_limit_date->format('Y-m-d'),
                'backgroundColor' => '#f59e0b',
                'borderColor' => '#d97706',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'requerimiento',
                    'registration_id' => $reg->id,
                ],
            ];
        }

        // Límites de respuesta (azul) - cuando hay response_limit_date pero NO es requerimiento
        $responseLimits = (clone $baseQuery)
            ->whereNotNull('response_limit_date')
            ->where(function ($q) {
                $q->where('status', '!=', 'requerimiento')
                  ->orWhereNull('status');
            })
            ->select('id', 'product_name', 'response_limit_date', 'company_id')
            ->get();

        foreach ($responseLimits as $reg) {
            // Verificación adicional: asegurar que pertenece a una empresa del cliente
            if (!$companyIds->contains($reg->company_id)) {
                continue;
            }
            
            $events[] = [
                'id' => 'resp-' . $reg->id,
                'title' => 'Límite respuesta: ' . \Str::limit($reg->product_name, 25),
                'start' => $reg->response_limit_date->format('Y-m-d'),
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#2563eb',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'response_limit',
                    'registration_id' => $reg->id,
                ],
            ];
        }

        return $events;
    }
}
