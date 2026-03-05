<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Lista de servicios activos para uso en cotizaciones (JSON).
     */
    public function listForQuotes(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'default_scope'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'default_scope' => $s->default_scope ?? '',
            ]);
        return response()->json($services->values()->all());
    }

    public function index(): View
    {
        $services = Service::orderBy('name')->paginate(20);
        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_scope' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        Service::create($validated);
        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_scope' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $service->update($validated);
        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Servicio eliminado.');
    }
}
