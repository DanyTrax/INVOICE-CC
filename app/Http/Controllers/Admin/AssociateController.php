<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssociateRequest;
use App\Http\Requests\UpdateAssociateRequest;
use App\Models\Associate;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssociateController extends Controller
{
    public function index(Request $request): View
    {
        if (! app(PermissionService::class)->userHasPermission('associates', 'view')) {
            abort(403);
        }

        $query = Associate::query()->orderBy('full_name');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('document_id', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        return view('admin.associates.index', [
            'associates' => $query->paginate(20)->withQueryString(),
            'categories' => Associate::categoryOptions(),
        ]);
    }

    public function create(): View
    {
        if (! app(PermissionService::class)->userHasPermission('associates', 'edit')) {
            abort(403);
        }

        return view('admin.associates.create', [
            'categories' => Associate::categoryOptions(),
        ]);
    }

    public function store(StoreAssociateRequest $request): RedirectResponse
    {
        Associate::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.associates.index')->with('success', 'Asociado creado correctamente.');
    }

    public function edit(Associate $associate): View
    {
        if (! app(PermissionService::class)->userHasPermission('associates', 'edit')) {
            abort(403);
        }

        return view('admin.associates.edit', [
            'associate' => $associate,
            'categories' => Associate::categoryOptions(),
        ]);
    }

    public function update(UpdateAssociateRequest $request, Associate $associate): RedirectResponse
    {
        $associate->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.associates.index')->with('success', 'Asociado actualizado.');
    }

    public function destroy(Associate $associate): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('associates', 'delete')) {
            abort(403);
        }

        if ($associate->invoices()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene cuentas de cobro asociadas.');
        }

        $associate->delete();

        return redirect()->route('admin.associates.index')->with('success', 'Asociado eliminado.');
    }
}
