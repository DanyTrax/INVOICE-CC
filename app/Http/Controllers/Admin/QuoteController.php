<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Process;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\ServiceType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public const STATUS_BORRADOR = 'Borrador';
    public const STATUS_ENVIADA = 'Enviada';
    public const STATUS_APROBADA = 'Aprobada';
    public const STATUS_RECHAZADA = 'Rechazada';
    public const STATUS_ANULADA = 'Anulada';

    /**
     * Estados en los que se puede aprobar la cotización.
     */
    public static function approvableStatuses(): array
    {
        return [self::STATUS_BORRADOR, self::STATUS_ENVIADA];
    }

    /**
     * Ver detalle de una cotización.
     */
    public function show(Quote $quote): View
    {
        $quote->load(['client', 'quoteItems.serviceType']);
        return view('admin.quotes.show', compact('quote'));
    }

    /**
     * Formulario de edición (solo si no está aprobada).
     */
    public function edit(Quote $quote): View|RedirectResponse
    {
        if ($quote->status === self::STATUS_APROBADA) {
            return redirect()->route('admin.quotes.show', $quote)
                ->with('error', 'La cotización está aprobada y no puede editarse.');
        }
        $quote->load(['client', 'quoteItems.serviceType']);
        $companies = Company::orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('name')->get();
        return view('admin.quotes.edit', compact('quote', 'companies', 'serviceTypes'));
    }

    /**
     * Actualizar cotización (solo si no está aprobada).
     */
    public function update(Request $request, Quote $quote): RedirectResponse
    {
        if ($quote->status === self::STATUS_APROBADA) {
            return redirect()->route('admin.quotes.show', $quote)
                ->with('error', 'La cotización está aprobada y no puede editarse.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:companies,id',
            'date' => 'required|date',
            'currency' => 'required|string|in:COP,USD',
            'exchange_rate' => 'nullable|numeric|min:0',
            'consecutive' => 'required|string|max:32|unique:quotes,consecutive,' . $quote->id,
            'show_prev_license_column' => 'nullable|boolean',
            'show_raa_column' => 'nullable|boolean',
            'apply_tax' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:quote_items,id',
            'items.*.item_position' => 'nullable|integer|min:0',
            'items.*.service_type_name' => 'required|string|max:255',
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

        $quote->update([
            'client_id' => $validated['client_id'],
            'consecutive' => $validated['consecutive'],
            'date' => $validated['date'],
            'currency' => $validated['currency'],
            'exchange_rate' => $validated['exchange_rate'] ?? null,
            'show_prev_license_column' => !empty($validated['show_prev_license_column']),
            'show_raa_column' => !empty($validated['show_raa_column']),
            'total_professional_fees' => round($totalFees, 2),
            'total_invima_fees' => round($totalInvima, 2),
            'total_loans' => round($totalLoans, 2),
            'apply_tax' => !empty($validated['apply_tax']),
            'tax_percentage' => isset($validated['tax_percentage']) ? round((float) $validated['tax_percentage'], 2) : null,
        ]);

        $existingIds = [];
        foreach ($validated['items'] as $pos => $row) {
            $serviceType = ServiceType::firstOrCreate(
                ['name' => $row['service_type_name']],
                ['is_active' => true]
            );
            $itemId = $row['id'] ?? null;
            $itemData = [
                'item_position' => (int) ($row['item_position'] ?? $pos + 1),
                'service_type_id' => $serviceType->id,
                'raa_code' => $row['raa_code'] ?? null,
                'previous_license' => $row['previous_license'] ?? null,
                'description' => $row['description'] ?? null,
                'scope' => $row['scope'] ?? null,
                'fee_value' => (float) ($row['fee_value'] ?? 0),
                'invima_rate_code' => $row['invima_rate_code'] ?? null,
                'invima_rate_value' => (float) ($row['invima_rate_value'] ?? 0),
                'is_loan' => !empty($row['is_loan']),
            ];
            if ($itemId && $quote->quoteItems()->where('id', $itemId)->exists()) {
                $quote->quoteItems()->where('id', $itemId)->update($itemData);
                $existingIds[] = $itemId;
            } else {
                $newItem = $quote->quoteItems()->create($itemData);
                $existingIds[] = $newItem->id;
            }
        }
        $quote->quoteItems()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('admin.quotes.show', $quote)->with('success', 'Cotización actualizada.');
    }

    /**
     * Aprobar cotización y crear procesos (uno por ítem que no sea préstamo).
     */
    public function approve(Quote $quote): RedirectResponse
    {
        if (!in_array($quote->status, self::approvableStatuses(), true)) {
            return redirect()->route('admin.quotes.show', $quote)
                ->with('error', 'Solo se puede aprobar una cotización en estado Borrador o Enviada.');
        }

        $quote->update(['status' => self::STATUS_APROBADA]);

        $itemsNoLoan = $quote->quoteItems()->where('is_loan', false)->orderBy('item_position')->get();
        $created = 0;
        foreach ($itemsNoLoan as $item) {
            if ($item->process()->exists()) {
                continue;
            }
            Process::create([
                'quote_item_id' => $item->id,
                'client_id' => $quote->client_id,
                'status' => Process::STATUS_RECOLECCION,
                'expediente_invima' => null,
            ]);
            $created++;
        }

        return redirect()
            ->route('admin.processes.index')
            ->with('success', "Cotización aprobada y {$created} proceso(s) creado(s).");
    }

    /**
     * Descargar cotización en PDF.
     */
    public function pdf(Quote $quote)
    {
        $quote->load(['client', 'quoteItems.serviceType']);
        $pdf = Pdf::loadView('admin.quotes.pdf', compact('quote'));
        $filename = 'cotizacion-' . preg_replace('/[^a-z0-9\-]/i', '-', $quote->consecutive) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Anular cotización (oferta rechazada por el cliente).
     */
    public function anular(Quote $quote): RedirectResponse
    {
        if ($quote->status === self::STATUS_APROBADA) {
            return redirect()->route('admin.quotes.show', $quote)
                ->with('error', 'No se puede anular una cotización ya aprobada.');
        }
        $quote->update(['status' => self::STATUS_ANULADA]);
        return redirect()->route('admin.quotes.show', $quote)
            ->with('success', 'Cotización anulada.');
    }

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

        // Sugerir consecutivo automático en formato NNN-AA (ej. 001-26)
        $year = now()->year % 100;
        $yearSuffix = str_pad((string) $year, 2, '0', STR_PAD_LEFT);
        $lastConsecutive = Quote::where('consecutive', 'like', '%-' . $yearSuffix)
            ->orderBy('consecutive', 'desc')
            ->value('consecutive');

        $nextNumber = 1;
        if ($lastConsecutive) {
            [$numberPart] = explode('-', $lastConsecutive);
            $nextNumber = (int) $numberPart + 1;
        }
        $suggestedConsecutive = sprintf('%03d-%s', $nextNumber, $yearSuffix);

        return view('admin.quotes.create', [
            'companies' => $companies,
            'serviceTypes' => $serviceTypes,
            'suggestedConsecutive' => $suggestedConsecutive,
        ]);
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
            'exchange_rate' => 'nullable|numeric|min:0',
            'consecutive' => 'required|string|max:32|unique:quotes,consecutive',
            'show_prev_license_column' => 'nullable|boolean',
            'show_raa_column' => 'nullable|boolean',
            'apply_tax' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.item_position' => 'nullable|integer|min:0',
            'items.*.service_type_name' => 'required|string|max:255',
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
            'exchange_rate' => $validated['exchange_rate'] ?? null,
            'show_prev_license_column' => !empty($validated['show_prev_license_column']),
            'show_raa_column' => !empty($validated['show_raa_column']),
            'status' => 'Borrador',
            'total_professional_fees' => round($totalFees, 2),
            'total_invima_fees' => round($totalInvima, 2),
            'total_loans' => round($totalLoans, 2),
            'apply_tax' => !empty($validated['apply_tax']),
            'tax_percentage' => isset($validated['tax_percentage']) ? round((float) $validated['tax_percentage'], 2) : null,
        ]);

        foreach ($validated['items'] as $pos => $row) {
            // Resolver o crear el tipo de trámite a partir del texto libre
            $serviceType = ServiceType::firstOrCreate(
                ['name' => $row['service_type_name']],
                ['is_active' => true]
            );

            QuoteItem::create([
                'quote_id' => $quote->id,
                'item_position' => (int) ($row['item_position'] ?? $pos + 1),
                'service_type_id' => $serviceType->id,
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
