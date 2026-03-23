<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConceptCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConceptCatalogController extends Controller
{
    public function index(): View
    {
        $concepts = ConceptCatalog::orderBy('name')->paginate(20);

        return view('admin.concept-catalogs.index', compact('concepts'));
    }

    public function create(): View
    {
        return view('admin.concept-catalogs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scope' => 'nullable|string|max:5000',
            'default_fee' => 'nullable|numeric|min:0',
        ]);

        ConceptCatalog::create([
            'name' => $validated['name'],
            'scope' => $validated['scope'] ?? null,
            'default_fee' => isset($validated['default_fee']) ? round((float) $validated['default_fee'], 2) : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.concept-catalogs.index')
            ->with('success', 'Concepto guardado en el catálogo.');
    }

    public function edit(ConceptCatalog $concept_catalog): View
    {
        return view('admin.concept-catalogs.edit', ['concept' => $concept_catalog]);
    }

    public function update(Request $request, ConceptCatalog $concept_catalog): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scope' => 'nullable|string|max:5000',
            'default_fee' => 'nullable|numeric|min:0',
        ]);

        $concept_catalog->update([
            'name' => $validated['name'],
            'scope' => $validated['scope'] ?? null,
            'default_fee' => isset($validated['default_fee']) ? round((float) $validated['default_fee'], 2) : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.concept-catalogs.index')
            ->with('success', 'Concepto actualizado.');
    }

    public function destroy(ConceptCatalog $concept_catalog): RedirectResponse
    {
        $concept_catalog->delete();

        return redirect()
            ->route('admin.concept-catalogs.index')
            ->with('success', 'Concepto eliminado.');
    }
}
