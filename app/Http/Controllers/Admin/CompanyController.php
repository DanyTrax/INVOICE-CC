<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nit_rut', 'like', "%{$search}%")
                  ->orWhere('contact_person_email', 'like', "%{$search}%");
            });
        }

        $companies = $query->withCount('registrations')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit_rut' => 'required|string|max:50|unique:companies,nit_rut',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
        ]);

        Company::create($validated);

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Company $company)
    {
        $company->loadCount('registrations');
        $company->load('registrations');
        
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nit_rut' => 'required|string|max:50|unique:companies,nit_rut,' . $company->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'logo_path' => 'nullable|string|max:500',
            'drive_folder_id' => 'nullable|string|max:255',
        ]);

        $company->update($validated);

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $companies = Company::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('nit_rut', 'like', "%{$query}%")
            ->select('id', 'name', 'email', 'nit_rut')
            ->limit(20)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'text' => $company->name . ' - ' . ($company->nit_rut ?: 'Sin NIT') . ($company->email ? ' (' . $company->email . ')' : ''),
                    'name' => $company->name,
                    'email' => $company->email,
                    'nit_rut' => $company->nit_rut,
                ];
            });
        
        return response()->json($companies);
    }

    public function destroy(Company $company)
    {
        // Verificar si tiene registros
        if ($company->registrations()->count() > 0) {
            return redirect()
                ->route('admin.companies.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene expedientes asociados.');
        }

        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
