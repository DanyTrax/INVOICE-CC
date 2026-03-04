<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::orderBy('name')->paginate(20);

        return view('admin.service-types.index', compact('serviceTypes'));
    }

    public function create()
    {
        return view('admin.service-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32|unique:service_types,code',
            'description' => 'nullable|string',
            'default_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        ServiceType::create($validated);

        return redirect()
            ->route('admin.service-types.index')
            ->with('success', 'Trámite creado correctamente.');
    }

    public function edit(ServiceType $serviceType)
    {
        return view('admin.service-types.edit', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32|unique:service_types,code,' . $serviceType->id,
            'description' => 'nullable|string',
            'default_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $serviceType->update($validated);

        return redirect()
            ->route('admin.service-types.index')
            ->with('success', 'Trámite actualizado correctamente.');
    }
}
