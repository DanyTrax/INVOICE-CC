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
        $companyIds = auth()->user()->companies()->pluck('companies.id');
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

        // Preparar eventos para el calendario visual (mes seleccionado o actual)
        $calendarEvents = [];
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);
        
        // Validar mes/año
        $selectedMonth = max(1, min(12, (int)$selectedMonth));
        $selectedYear = max(2020, min(2100, (int)$selectedYear));
        
        foreach ($calendarRegistrations as $reg) {
            $isRequerimiento = $reg->status === 'requerimiento';
            $useResponseLimit = $isRequerimiento && $reg->response_limit_date;
            $fecha = $useResponseLimit ? $reg->response_limit_date : ($reg->expiration_date ?? $reg->response_limit_date);
            
            if ($fecha && $fecha->month == $selectedMonth && $fecha->year == $selectedYear) {
                $day = $fecha->day;
                if (!isset($calendarEvents[$day])) {
                    $calendarEvents[$day] = [];
                }
                
                if ($isRequerimiento && $reg->response_limit_date) {
                    $calendarEvents[$day][] = [
                        'type' => 'requerimiento',
                        'text' => 'Requerimiento: ' . $reg->product_name,
                        'color' => 'amber',
                        'registration' => $reg,
                    ];
                } elseif ($reg->expiration_date) {
                    $calendarEvents[$day][] = [
                        'type' => 'vencimiento',
                        'text' => 'Vence: ' . $reg->product_name,
                        'color' => 'red',
                        'registration' => $reg,
                    ];
                } else {
                    $calendarEvents[$day][] = [
                        'type' => 'limite',
                        'text' => 'Límite respuesta: ' . $reg->product_name,
                        'color' => 'blue',
                        'registration' => $reg,
                    ];
                }
            }
        }

        return view('portal.dashboard', compact(
            'vigentes', 'enTramite', 'requerimiento', 'vencidos', 'proximosVencer',
            'registrations', 'calendarRegistrations', 'calendarEvents', 
            'selectedMonth', 'selectedYear'
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
}
