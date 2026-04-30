<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
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
            'description' => 'nullable|string',
        ]);

        ServiceType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'code' => null,
            'default_price' => null,
            'is_active' => true,
        ]);

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
            'description' => 'nullable|string',
        ]);

        $serviceType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.service-types.index')
            ->with('success', 'Trámite actualizado correctamente.');
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        $quoteItemsCount = $serviceType->quoteItems()->count();
        $processesCount = Process::query()->where('service_type_id', $serviceType->id)->count();

        if ($quoteItemsCount > 0 || $processesCount > 0) {
            $parts = [];
            if ($quoteItemsCount > 0) {
                $parts[] = $quoteItemsCount === 1
                    ? '1 ítem en cotizaciones'
                    : "{$quoteItemsCount} ítems en cotizaciones";
            }
            if ($processesCount > 0) {
                $parts[] = $processesCount === 1
                    ? '1 solicitud'
                    : "{$processesCount} solicitudes";
            }

            return redirect()
                ->route('admin.service-types.index')
                ->with('error', 'No se puede eliminar este trámite porque aún está en uso ('.implode(' y ', $parts).'). Edite las cotizaciones para quitar o cambiar el trámite en esos ítems, o elimine o reasigne las solicitudes.');
        }

        $serviceType->delete();

        return redirect()
            ->route('admin.service-types.index')
            ->with('success', 'Trámite eliminado correctamente.');
    }
}
