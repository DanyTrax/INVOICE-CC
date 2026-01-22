<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['company', 'assignedSpecialist']);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('quotation_number', 'like', "%{$search}%")
                  ->orWhere('radication_number', 'like', "%{$search}%")
                  ->orWhereHas('company', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por empresa
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por vencimientos este mes
        if ($request->filled('filter') && $request->filter === 'expiring') {
            $query->whereMonth('expiration_date', now()->month)
                  ->whereYear('expiration_date', now()->year);
        }

        // Filtro por especialista
        if ($request->filled('specialist')) {
            $query->where('assigned_specialist_id', $request->specialist);
        }

        $registrations = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        $companies = Company::orderBy('name')->get();
        $specialists = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.registrations.index', compact('registrations', 'companies', 'specialists'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $specialists = User::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.registrations.create', compact('companies', 'specialists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'assigned_specialist_id' => 'nullable|exists:users,id',
            'product_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'status' => 'required|string|max:50',
            'transaction_type' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'client_request_date' => 'nullable|date',
            'radication_date' => 'nullable|date',
            'submission_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'invima_auto_date' => 'nullable|date',
            'response_limit_date' => 'nullable|date',
            'response_radication_date' => 'nullable|date',
            'client_requirement' => 'nullable|string',
            'invima_requirement' => 'nullable|string',
            'pending_docs' => 'nullable|string',
            'observations' => 'nullable|string',
            'radication_number' => 'nullable|string|max:100',
            'key_code' => 'nullable|string|max:100',
            'resolution_number' => 'nullable|string|max:100',
            'drive_folder_url' => 'nullable|string|max:500',
        ]);

        Registration::create($validated);

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente creado exitosamente.');
    }

    public function show(Registration $registration)
    {
        $registration->load(['company', 'assignedSpecialist', 'documents']);
        
        return view('admin.registrations.show', compact('registration'));
    }

    public function edit(Registration $registration)
    {
        $companies = Company::orderBy('name')->get();
        $specialists = User::where('is_active', true)->orderBy('name')->get();
        $registration->load(['company', 'assignedSpecialist']);
        
        return view('admin.registrations.edit', compact('registration', 'companies', 'specialists'));
    }

    public function update(Request $request, Registration $registration)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'assigned_specialist_id' => 'nullable|exists:users,id',
            'product_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'status' => 'required|string|max:50',
            'transaction_type' => 'nullable|string|max:100',
            'quotation_number' => 'nullable|string|max:100',
            'client_request_date' => 'nullable|date',
            'radication_date' => 'nullable|date',
            'submission_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'invima_auto_date' => 'nullable|date',
            'response_limit_date' => 'nullable|date',
            'response_radication_date' => 'nullable|date',
            'client_requirement' => 'nullable|string',
            'invima_requirement' => 'nullable|string',
            'pending_docs' => 'nullable|string',
            'observations' => 'nullable|string',
            'radication_number' => 'nullable|string|max:100',
            'key_code' => 'nullable|string|max:100',
            'resolution_number' => 'nullable|string|max:100',
            'drive_folder_url' => 'nullable|string|max:500',
        ]);

        $registration->update($validated);

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente actualizado exitosamente.');
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Expediente eliminado exitosamente.');
    }
}
