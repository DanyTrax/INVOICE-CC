<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class QuoteController extends Controller
{
    /**
     * Listado de cotizaciones.
     */
    public function index(Request $request)
    {
        $query = Quote::with(['client', 'quoteItems.serviceType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('consecutive', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotes = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    /**
     * Formulario para crear una nueva cotización.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('name')->get();

        return view('admin.quotes.create', compact('companies', 'serviceTypes'));
    }

    /**
     * Guardar cabecera e ítems de la cotización.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:companies,id',
            'date' => 'required|date',
            'currency' => 'required|string|in:COP,USD',
            'consecutive' => 'required|string|max:32|unique:quotes,consecutive',
            'items' => 'required|array|min:1',
            'items.*.item_position' => 'nullable|integer|min:0',
            'items.*.service_type_id' => 'required|exists:service_types,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.previous_license' => 'nullable|string|max:64',
            'items.*.raa_code' => 'nullable|string|max:64',
            'items.*.scope' => 'nullable|string|max:1000',
            'items.*.fee_value' => 'required|numeric|min:0',
            'items.*.invima_rate_code' => 'nullable|string|max:32',
            'items.*.invima_rate_value' => 'nullable|numeric|min:0',
            'items.*.is_loan' => 'nullable|boolean',
        ]);

        $totalFees = 0;
        $totalLoans = 0;
        $totalInvima = 0;
        foreach ($validated['items'] as $pos => $row) {
            $val = (float) ($row['fee_value'] ?? 0);
            $isLoan = !empty($row['is_loan']);
            if ($isLoan) {
                $totalLoans += $val;
            } else {
                $totalFees += $val;
            }
            $totalInvima += (float) ($row['invima_rate_value'] ?? 0);
        }

        $quote = Quote::create([
            'client_id' => $validated['client_id'],
            'consecutive' => $validated['consecutive'],
            'date' => $validated['date'],
            'currency' => $validated['currency'],
            'status' => 'Borrador',
            'total_professional_fees' => round($totalFees, 2),
            'total_invima_fees' => round($totalInvima, 2),
            'total_loans' => round($totalLoans, 2),
        ]);

        foreach ($validated['items'] as $pos => $row) {
            QuoteItem::create([
                'quote_id' => $quote->id,
                'item_position' => (int) ($row['item_position'] ?? $pos + 1),
                'service_type_id' => $row['service_type_id'],
                'raa_code' => $row['raa_code'] ?? null,
                'previous_license' => $row['previous_license'] ?? null,
                'description' => $row['description'] ?? null,
                'scope' => $row['scope'] ?? null,
                'fee_value' => (float) ($row['fee_value'] ?? 0),
                'invima_rate_code' => $row['invima_rate_code'] ?? null,
                'invima_rate_value' => (float) ($row['invima_rate_value'] ?? 0),
                'is_loan' => !empty($row['is_loan']),
            ]);
        }

        return redirect()
            ->route('admin.quotes.index')
            ->with('success', 'Cotización creada correctamente.');
    }
}
