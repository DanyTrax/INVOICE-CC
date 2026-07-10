<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Associate;
use App\Models\Concept;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'view')) {
            abort(403);
        }

        $query = Invoice::query()
            ->with(['associate', 'concept'])
            ->orderByDesc('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', '%'.$search.'%')
                    ->orWhereHas('associate', fn ($a) => $a->where('full_name', 'like', '%'.$search.'%'));
            });
        }

        if ($dueFrom = $request->input('due_from')) {
            $query->whereDate('due_date', '>=', $dueFrom);
        }

        if ($dueTo = $request->input('due_to')) {
            $query->whereDate('due_date', '<=', $dueTo);
        }

        $permService = app(PermissionService::class);

        return view('admin.invoices.index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'statuses' => Invoice::STATUSES,
            'canDelete' => $permService->userHasPermission('invoices', 'delete'),
        ]);
    }

    public function create(): View
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'edit')) {
            abort(403);
        }

        return view('admin.invoices.create', [
            'associates' => Associate::query()->where('is_active', true)->orderBy('full_name')->get(),
            'concepts' => Concept::query()->where('is_active', true)->with('prices')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreInvoiceRequest $request, InvoiceService $service): RedirectResponse
    {
        try {
            $invoice = $service->createInvoice($request->validated(), (int) auth()->id());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Cuenta de cobro creada.');
    }

    public function show(Invoice $invoice): View
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'view')) {
            abort(403);
        }

        $invoice->load(['associate', 'concept', 'createdBy']);

        return view('admin.invoices.show', [
            'invoice' => $invoice,
            'statuses' => Invoice::STATUSES,
            'canDelete' => app(PermissionService::class)->userHasPermission('invoices', 'delete'),
        ]);
    }

    public function edit(Invoice $invoice): View|RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'edit')) {
            abort(403);
        }

        if (! $invoice->isEditable()) {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Solo se pueden editar cuentas de cobro en borrador.');
        }

        return view('admin.invoices.edit', [
            'invoice' => $invoice->load(['associate', 'concept']),
            'associates' => Associate::query()->where('is_active', true)->orderBy('full_name')->get(),
            'concepts' => Concept::query()->where('is_active', true)->with('prices')->orderBy('name')->get(),
            'statuses' => Invoice::STATUSES,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        try {
            $service->updateInvoice($invoice, $request->validated());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Cuenta de cobro actualizada.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'delete')) {
            abort(403);
        }

        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Cuenta de cobro eliminada.');
    }

    public function pdf(Invoice $invoice, InvoiceService $service): Response
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'view')) {
            abort(403);
        }

        $filename = 'cuenta-cobro-'.preg_replace('/[^A-Za-z0-9._-]+/', '-', $invoice->number).'.pdf';

        return $service->makePdf($invoice)->download($filename);
    }

    public function send(Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'edit')) {
            abort(403);
        }

        try {
            $service->sendByEmail($invoice);
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo enviar el correo: '.$e->getMessage());
        }

        return back()->with('success', 'Cuenta de cobro enviada por correo.');
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('invoices', 'edit')) {
            abort(403);
        }

        $invoice->update([
            'status' => Invoice::STATUS_PAID,
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Cuenta de cobro marcada como pagada.');
    }
}
