<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConceptRequest;
use App\Http\Requests\UpdateConceptRequest;
use App\Models\Associate;
use App\Models\Concept;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConceptController extends Controller
{
    public function index(Request $request): View
    {
        if (! app(PermissionService::class)->userHasPermission('concepts', 'view')) {
            abort(403);
        }

        $query = Concept::query()->with('prices')->orderByDesc('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return view('admin.concepts.index', [
            'concepts' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        if (! app(PermissionService::class)->userHasPermission('concepts', 'edit')) {
            abort(403);
        }

        return view('admin.concepts.create', [
            'categories' => Associate::categoryOptions(),
        ]);
    }

    public function store(StoreConceptRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $concept = Concept::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncPrices($concept, $request->input('prices', []));
        });

        return redirect()->route('admin.concepts.index')->with('success', 'Concepto creado correctamente.');
    }

    public function edit(Concept $concept): View
    {
        if (! app(PermissionService::class)->userHasPermission('concepts', 'edit')) {
            abort(403);
        }

        $concept->load('prices');

        return view('admin.concepts.edit', [
            'concept' => $concept,
            'categories' => Associate::categoryOptions(),
        ]);
    }

    public function update(UpdateConceptRequest $request, Concept $concept): RedirectResponse
    {
        DB::transaction(function () use ($request, $concept) {
            $concept->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncPrices($concept, $request->input('prices', []));
        });

        return redirect()->route('admin.concepts.index')->with('success', 'Concepto actualizado.');
    }

    public function destroy(Concept $concept): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('concepts', 'delete')) {
            abort(403);
        }

        if ($concept->invoices()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene cuentas de cobro asociadas.');
        }

        $concept->delete();

        return redirect()->route('admin.concepts.index')->with('success', 'Concepto eliminado.');
    }

    /**
     * @param  array<int, array{category: string, amount: mixed}>  $prices
     */
    private function syncPrices(Concept $concept, array $prices): void
    {
        $concept->prices()->delete();

        foreach ($prices as $row) {
            if (! isset($row['category'], $row['amount'])) {
                continue;
            }
            $concept->prices()->create([
                'category' => $row['category'],
                'amount' => $row['amount'],
            ]);
        }
    }
}
