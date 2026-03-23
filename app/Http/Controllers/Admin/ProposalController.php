<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ConceptCatalog;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalPdfTemplate;
use App\Settings\GeneralSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Proposal::with(['client']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('consecutive', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $proposals = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        return view('admin.proposals.index', compact('proposals'));
    }

    public function create(): View
    {
        $companies = Company::orderBy('name')->get();
        $conceptCatalog = ConceptCatalog::active()->orderBy('name')->get();
        $suggestedConsecutive = $this->nextConsecutive();

        return view('admin.proposals.create', compact('companies', 'conceptCatalog', 'suggestedConsecutive'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProposal($request);

        $totalFees = 0;
        foreach ($validated['items'] as $row) {
            $totalFees += (float) ($row['fee_value'] ?? 0);
        }

        $proposal = Proposal::create([
            'client_id' => $validated['client_id'],
            'consecutive' => $validated['consecutive'],
            'date' => $validated['date'],
            'currency' => $validated['currency'],
            'exchange_rate' => $validated['exchange_rate'] ?? null,
            'status' => Proposal::STATUS_PENDIENTE,
            'total_professional_fees' => round($totalFees, 2),
            'apply_tax' => !empty($validated['apply_tax']),
            'tax_percentage' => isset($validated['tax_percentage']) ? round((float) $validated['tax_percentage'], 2) : null,
            'apply_bank_fee' => !empty($validated['apply_bank_fee']),
            'bank_fee_value' => !empty($validated['apply_bank_fee']) && isset($validated['bank_fee_value']) ? round((float) $validated['bank_fee_value'], 2) : null,
        ]);

        foreach ($validated['items'] as $pos => $row) {
            ProposalItem::create([
                'proposal_id' => $proposal->id,
                'concept_catalog_id' => !empty($row['concept_catalog_id']) ? (int) $row['concept_catalog_id'] : null,
                'item_position' => (int) ($row['item_position'] ?? $pos + 1),
                'concept' => $row['concept'],
                'scope' => $row['scope'] ?? null,
                'fee_value' => (float) ($row['fee_value'] ?? 0),
            ]);
        }

        return redirect()
            ->route('admin.proposals.show', $proposal)
            ->with('success', 'Propuesta creada correctamente.');
    }

    public function show(Proposal $proposal): View
    {
        $proposal->load(['client', 'proposalItems.conceptCatalog']);
        try {
            $proposalPdfTemplates = ProposalPdfTemplate::orderByRaw('is_default DESC')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $proposalPdfTemplates = collect();
        }

        return view('admin.proposals.show', compact('proposal', 'proposalPdfTemplates'));
    }

    public function edit(Proposal $proposal): View|RedirectResponse
    {
        if ($proposal->status === Proposal::STATUS_APROBADA) {
            return redirect()->route('admin.proposals.show', $proposal)
                ->with('error', 'La propuesta está aprobada y no puede editarse.');
        }

        $proposal->load(['client', 'proposalItems']);
        $companies = Company::orderBy('name')->get();
        $conceptCatalog = ConceptCatalog::active()->orderBy('name')->get();

        return view('admin.proposals.edit', compact('proposal', 'companies', 'conceptCatalog'));
    }

    public function update(Request $request, Proposal $proposal): RedirectResponse
    {
        if ($proposal->status === Proposal::STATUS_APROBADA) {
            return redirect()->route('admin.proposals.show', $proposal)
                ->with('error', 'La propuesta está aprobada y no puede editarse.');
        }

        $validated = $this->validateProposal($request, $proposal->id);

        $totalFees = 0;
        foreach ($validated['items'] as $row) {
            $totalFees += (float) ($row['fee_value'] ?? 0);
        }

        $proposal->update([
            'client_id' => $validated['client_id'],
            'consecutive' => $validated['consecutive'],
            'date' => $validated['date'],
            'currency' => $validated['currency'],
            'exchange_rate' => $validated['exchange_rate'] ?? null,
            'total_professional_fees' => round($totalFees, 2),
            'apply_tax' => !empty($validated['apply_tax']),
            'tax_percentage' => isset($validated['tax_percentage']) ? round((float) $validated['tax_percentage'], 2) : null,
            'apply_bank_fee' => !empty($validated['apply_bank_fee']),
            'bank_fee_value' => !empty($validated['apply_bank_fee']) && isset($validated['bank_fee_value']) ? round((float) $validated['bank_fee_value'], 2) : null,
        ]);

        $existingIds = [];
        foreach ($validated['items'] as $pos => $row) {
            $itemData = [
                'concept_catalog_id' => !empty($row['concept_catalog_id']) ? (int) $row['concept_catalog_id'] : null,
                'item_position' => (int) ($row['item_position'] ?? $pos + 1),
                'concept' => $row['concept'],
                'scope' => $row['scope'] ?? null,
                'fee_value' => (float) ($row['fee_value'] ?? 0),
            ];
            $itemId = $row['id'] ?? null;
            if ($itemId && $proposal->proposalItems()->where('id', $itemId)->exists()) {
                $proposal->proposalItems()->where('id', $itemId)->update($itemData);
                $existingIds[] = $itemId;
            } else {
                $new = $proposal->proposalItems()->create($itemData);
                $existingIds[] = $new->id;
            }
        }
        $proposal->proposalItems()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('admin.proposals.show', $proposal)->with('success', 'Propuesta actualizada.');
    }

    public function approve(Proposal $proposal): RedirectResponse
    {
        if ($proposal->status === Proposal::STATUS_APROBADA) {
            return redirect()->route('admin.proposals.show', $proposal)->with('success', 'La propuesta ya estaba aprobada.');
        }

        $proposal->update(['status' => Proposal::STATUS_APROBADA]);

        return redirect()->route('admin.proposals.show', $proposal)->with('success', 'Propuesta aprobada.');
    }

    public function pdf(Proposal $proposal, Request $request)
    {
        $proposal->load(['client', 'proposalItems']);
        $template = null;
        if ($request->filled('template_id')) {
            $template = ProposalPdfTemplate::find($request->template_id);
        }
        if (!$template) {
            $template = ProposalPdfTemplate::getDefault();
        }
        $settings = app(GeneralSettings::class);
        $pdf = Pdf::loadView('admin.proposals.pdf', compact('proposal', 'settings', 'template'));
        $filename = 'propuesta-' . preg_replace('/[^a-z0-9\-]/i', '-', $proposal->consecutive) . '.pdf';

        return $pdf->download($filename);
    }

    public function updatePdfFooter(Request $request, Proposal $proposal): RedirectResponse
    {
        $validated = $request->validate([
            'pdf_footer' => 'nullable|string|max:1000',
        ]);
        $proposal->update([
            'pdf_footer' => $validated['pdf_footer'] ?? null,
        ]);

        return redirect()->route('admin.proposals.show', $proposal)
            ->with('success', 'Pie de página del PDF actualizado.');
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $proposal->delete();

        return redirect()->route('admin.proposals.index')->with('success', 'Propuesta eliminada.');
    }

    protected function validateProposal(Request $request, ?int $proposalId = null): array
    {
        $consecutiveRule = 'required|string|max:32|unique:proposals,consecutive';
        if ($proposalId) {
            $consecutiveRule .= ',' . $proposalId;
        }

        return $request->validate([
            'client_id' => 'required|exists:companies,id',
            'date' => 'required|date',
            'currency' => 'required|string|in:COP,USD',
            'exchange_rate' => 'nullable|numeric|min:0',
            'consecutive' => $consecutiveRule,
            'apply_tax' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'apply_bank_fee' => 'nullable|boolean',
            'bank_fee_value' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:proposal_items,id',
            'items.*.item_position' => 'nullable|integer|min:0',
            'items.*.concept_catalog_id' => 'nullable|exists:concept_catalogs,id',
            'items.*.concept' => 'required|string|max:500',
            'items.*.scope' => 'nullable|string|max:5000',
            'items.*.fee_value' => 'required|numeric|min:0',
        ]);
    }

    protected function nextConsecutive(): string
    {
        $year = now()->year % 100;
        $yearSuffix = str_pad((string) $year, 2, '0', STR_PAD_LEFT);
        $lastConsecutive = Proposal::where('consecutive', 'like', 'P-%' . $yearSuffix)
            ->orderBy('consecutive', 'desc')
            ->value('consecutive');

        $nextNumber = 1;
        if ($lastConsecutive && preg_match('/^P-(\d+)-/', $lastConsecutive, $m)) {
            $nextNumber = (int) $m[1] + 1;
        }

        return sprintf('P-%03d-%s', $nextNumber, $yearSuffix);
    }
}
