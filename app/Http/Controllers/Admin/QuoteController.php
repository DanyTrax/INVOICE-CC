<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Process;
use App\Models\Quote;
use App\Models\QuotePdfTemplate;
use App\Models\Service;
use App\Models\ServiceType;
use App\Services\CompanyConsecutiveService;
use App\Support\PdfDocumentHelper;
use App\Settings\GeneralSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        $quote->load([
            'client',
            'quoteItems.service',
            'quoteItems.serviceType',
            // Solicitud (process) creada desde el ítem (HasOne) y ciclos vinculados al ítem
            'quoteItems.process.serviceType',
            'quoteItems.submissions.process.serviceType',
        ]);
        try {
            $quotePdfTemplates = QuotePdfTemplate::orderByRaw('is_default DESC')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $quotePdfTemplates = collect();
        }

        $defaultPdfTemplate = QuotePdfTemplate::getDefault();

        return view('admin.quotes.show', compact('quote', 'quotePdfTemplates', 'defaultPdfTemplate'));
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
        $quote->load([
            'client',
            'quoteItems.serviceType',
            'quoteItems.service',
            'quoteItems.process',
            'quoteItems.submissions.process',
        ]);
        $companies = Company::orderBy('name')->get();
        $serviceTypes = ServiceType::orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        $defaultPdfTemplate = QuotePdfTemplate::getDefault();

        return view('admin.quotes.edit', compact('quote', 'companies', 'serviceTypes', 'services', 'defaultPdfTemplate'));
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
            'consecutive' => [
                'required',
                'string',
                'max:32',
                Rule::unique('quotes', 'consecutive')->where(fn ($q) => $q->where('client_id', $request->input('client_id')))->ignore($quote->id),
            ],
            'show_prev_license_column' => 'nullable|boolean',
            'show_raa_column' => 'nullable|boolean',
            'show_service_type_column' => 'nullable|boolean',
            'show_description_column' => 'nullable|boolean',
            'show_row_id_column' => 'nullable|boolean',
            'show_franquicia_column' => 'nullable|boolean',
            'show_centro_costos_column' => 'nullable|boolean',
            'show_contacto_column' => 'nullable|boolean',
            'apply_tax' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'apply_bank_fee' => 'nullable|boolean',
            'bank_fee_value' => 'nullable|numeric|min:0',
            'pdf_body_html' => 'nullable|string',
            'pdf_side_note_html' => 'nullable|string',
            'pdf_footer' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:quote_items,id',
            'items.*.item_position' => 'nullable|integer|min:0',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.service_label' => 'nullable|string|max:255',
            'items.*.service_type_name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.previous_license' => 'nullable|string|max:64',
            'items.*.raa_code' => 'nullable|string|max:64',
            'items.*.row_id' => 'nullable|string|max:128',
            'items.*.franquicia' => 'nullable|string|max:255',
            'items.*.centro_costos' => 'nullable|string|max:255',
            'items.*.contacto' => 'nullable|string|max:255',
            'items.*.scope' => 'nullable|string|max:1000',
            'items.*.fee_value' => 'required|numeric|min:0',
            'items.*.invima_rate_code' => 'nullable|string|max:32',
            'items.*.invima_rate_value' => 'nullable|numeric|min:0',
        ], [
            'items.*.service_id.required' => 'Cada ítem debe tener un servicio elegido de la lista (escriba y seleccione uno existente).',
            'items.*.service_id.exists' => 'El servicio no es válido. Debe elegirse exactamente uno de la lista de servicios.',
        ]);

        $totalFees = 0;
        $totalInvima = 0;
        foreach ($validated['items'] as $pos => $row) {
            $totalFees += (float) ($row['fee_value'] ?? 0);
            $totalInvima += (float) ($row['invima_rate_value'] ?? 0);
        }

        $pdfFields = PdfDocumentHelper::persistPdfTextFields($validated, QuotePdfTemplate::getDefault());

        $quote->update([
            'client_id' => $validated['client_id'],
            'consecutive' => $validated['consecutive'],
            'date' => $validated['date'],
            'currency' => $validated['currency'],
            'exchange_rate' => $validated['exchange_rate'] ?? null,
            'show_prev_license_column' => ! empty($validated['show_prev_license_column']),
            'show_raa_column' => ! empty($validated['show_raa_column']),
            'show_service_type_column' => ! empty($validated['show_service_type_column']),
            'show_description_column' => array_key_exists('show_description_column', $validated) ? ! empty($validated['show_description_column']) : true,
            'show_row_id_column' => ! empty($validated['show_row_id_column']),
            'show_franquicia_column' => ! empty($validated['show_franquicia_column']),
            'show_centro_costos_column' => ! empty($validated['show_centro_costos_column']),
            'show_contacto_column' => ! empty($validated['show_contacto_column']),
            'total_professional_fees' => round($totalFees, 2),
            'total_invima_fees' => round($totalInvima, 2),
            'apply_tax' => ! empty($validated['apply_tax']),
            'tax_percentage' => isset($validated['tax_percentage']) ? round((float) $validated['tax_percentage'], 2) : null,
            'apply_bank_fee' => ! empty($validated['apply_bank_fee']),
            'bank_fee_value' => ! empty($validated['apply_bank_fee']) && isset($validated['bank_fee_value']) ? round((float) $validated['bank_fee_value'], 2) : null,
            'pdf_body_html' => $pdfFields['pdf_body_html'],
            'pdf_side_note_html' => $pdfFields['pdf_side_note_html'],
            'pdf_footer' => $pdfFields['pdf_footer'],
        ]);

        $existingIds = [];
        foreach ($validated['items'] as $pos => $row) {
            $serviceTypeName = trim($row['service_type_name'] ?? '');
            if ($serviceTypeName === '') {
                $serviceTypeName = 'Sin trámite especificado';
            }
            $serviceType = ServiceType::firstOrCreate(
                ['name' => $serviceTypeName],
                ['is_active' => true]
            );
            $itemId = $row['id'] ?? null;
            $itemData = [
                'item_position' => (int) ($row['item_position'] ?? $pos + 1),
                'row_id' => isset($row['row_id']) && trim((string) $row['row_id']) !== '' ? trim((string) $row['row_id']) : null,
                'service_id' => $row['service_id'] ?? null,
                'service_label' => isset($row['service_label']) && trim((string) $row['service_label']) !== '' ? trim((string) $row['service_label']) : null,
                'service_type_id' => $serviceType->id,
                'raa_code' => $row['raa_code'] ?? null,
                'franquicia' => isset($row['franquicia']) && trim((string) $row['franquicia']) !== '' ? trim((string) $row['franquicia']) : null,
                'centro_costos' => isset($row['centro_costos']) && trim((string) $row['centro_costos']) !== '' ? trim((string) $row['centro_costos']) : null,
                'contacto' => isset($row['contacto']) && trim((string) $row['contacto']) !== '' ? trim((string) $row['contacto']) : null,
                'previous_license' => $row['previous_license'] ?? null,
                'description' => $row['description'] ?? null,
                'scope' => $row['scope'] ?? null,
                'fee_value' => (float) ($row['fee_value'] ?? 0),
                'invima_rate_code' => $row['invima_rate_code'] ?? null,
                'invima_rate_value' => (float) ($row['invima_rate_value'] ?? 0),
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
     * Aprobar cotización (sin crear procesos automáticamente).
     */
    public function approve(Quote $quote): RedirectResponse
    {
        if (! in_array($quote->status, self::approvableStatuses(), true)) {
            return redirect()->route('admin.quotes.show', $quote)
                ->with('error', 'Solo se puede aprobar una cotización en estado Borrador o Enviada.');
        }

        $quote->update(['status' => self::STATUS_APROBADA]);

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'Cotización aprobada. Ahora puedes crear y vincular solicitudes desde el módulo Solicitudes / Procesos.');
    }

    /**
     * Descargar cotización en PDF.
     */
    public function pdf(Quote $quote, Request $request)
    {
        $quote->load(['client', 'quoteItems.service', 'quoteItems.serviceType', 'quoteItems.process.serviceType']);
        $template = null;
        if ($request->filled('template_id')) {
            $template = QuotePdfTemplate::find($request->template_id);
        }
        if (! $template) {
            $template = QuotePdfTemplate::getDefault();
        }
        $settings = app(GeneralSettings::class);
        $pdf = Pdf::loadView('admin.quotes.pdf', compact('quote', 'settings', 'template'));
        $filename = 'cotizacion-'.preg_replace('/[^a-z0-9\-]/i', '-', $quote->consecutive).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Actualizar solo el texto del pie de página del PDF de la cotización.
     */
    public function updatePdfFooter(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'pdf_body_html' => 'nullable|string',
            'pdf_side_note_html' => 'nullable|string',
            'pdf_footer' => 'nullable|string|max:2000',
        ]);
        $quote->update(PdfDocumentHelper::persistPdfTextFields($validated, QuotePdfTemplate::getDefault()));

        return redirect()->route('admin.quotes.show', $quote)
            ->with('success', 'Textos del PDF actualizados.');
    }

    /**
     * Anular cotización (oferta rechazada por el cliente).
     */
    public function anular(Request $request, Quote $quote): RedirectResponse
    {
        if ($quote->status === self::STATUS_APROBADA) {
            return redirect()->route('admin.quotes.show', $quote)
                ->with('error', 'No se puede anular una cotización ya aprobada.');
        }
        $validated = $request->validate([
            'cancellation_note' => 'required|string|max:2000',
        ]);
        $quote->update([
            'status' => self::STATUS_ANULADA,
            'cancellation_note' => $validated['cancellation_note'],
        ]);

        return redirect()->route('admin.quotes.show', $quote)
            ->with('success', 'Cotización anulada.');
    }

    /**
     * Eliminar cotización (en cualquier estado). Se eliminan también sus ítems.
     * Las solicitudes vinculadas a ítems de esta cotización quedan sin cotización (quote_id/quote_item_id a null).
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()
            ->route('admin.quotes.index')
            ->with('success', 'Cotización eliminada.');
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

        $quotes = $query->join('companies', 'quotes.client_id', '=', 'companies.id')
            ->orderBy('companies.name')
            ->orderBy('quotes.consecutive')
            ->select('quotes.*')
            ->paginate(15)
            ->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    /**
     * Formulario para crear una nueva cotización.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $serviceTypes = ServiceType::orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $defaultPdfTemplate = QuotePdfTemplate::getDefault();

        return view('admin.quotes.create', [
            'companies' => $companies,
            'serviceTypes' => $serviceTypes,
            'services' => $services,
            'suggestedConsecutive' => '',
            'defaultPdfTemplate' => $defaultPdfTemplate,
        ]);
    }

    /**
     * Sugerir consecutivo de cotización según siglas del cliente (JSON).
     */
    public function suggestConsecutive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:companies,id',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            $year = isset($validated['year']) ? (int) $validated['year'] : null;
            $consecutive = CompanyConsecutiveService::suggestQuoteConsecutive((int) $validated['client_id'], $year);

            return response()->json(['consecutive' => $consecutive]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => collect($e->errors())->flatten()->first() ?? 'Siglas no válidas.',
            ], 422);
        }
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
            'consecutive' => [
                'required',
                'string',
                'max:32',
                Rule::unique('quotes', 'consecutive')->where(fn ($q) => $q->where('client_id', $request->input('client_id'))),
            ],
            'show_prev_license_column' => 'nullable|boolean',
            'show_raa_column' => 'nullable|boolean',
            'show_service_type_column' => 'nullable|boolean',
            'show_description_column' => 'nullable|boolean',
            'show_row_id_column' => 'nullable|boolean',
            'show_franquicia_column' => 'nullable|boolean',
            'show_centro_costos_column' => 'nullable|boolean',
            'show_contacto_column' => 'nullable|boolean',
            'apply_tax' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'apply_bank_fee' => 'nullable|boolean',
            'bank_fee_value' => 'nullable|numeric|min:0',
            'pdf_body_html' => 'nullable|string',
            'pdf_side_note_html' => 'nullable|string',
            'pdf_footer' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.item_position' => 'nullable|integer|min:0',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.service_label' => 'nullable|string|max:255',
            'items.*.service_type_name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.previous_license' => 'nullable|string|max:64',
            'items.*.raa_code' => 'nullable|string|max:64',
            'items.*.row_id' => 'nullable|string|max:128',
            'items.*.franquicia' => 'nullable|string|max:255',
            'items.*.centro_costos' => 'nullable|string|max:255',
            'items.*.contacto' => 'nullable|string|max:255',
            'items.*.scope' => 'nullable|string|max:1000',
            'items.*.fee_value' => 'required|numeric|min:0',
            'items.*.invima_rate_code' => 'nullable|string|max:32',
            'items.*.invima_rate_value' => 'nullable|numeric|min:0',
        ], [
            'items.*.service_id.required' => 'Cada ítem debe tener un servicio elegido de la lista (escriba y seleccione uno existente).',
            'items.*.service_id.exists' => 'El servicio no es válido. Debe elegirse exactamente uno de la lista de servicios.',
        ]);

        $totalFees = 0;
        $totalInvima = 0;
        foreach ($validated['items'] as $pos => $row) {
            $totalFees += (float) ($row['fee_value'] ?? 0);
            $totalInvima += (float) ($row['invima_rate_value'] ?? 0);
        }

        $pdfFields = PdfDocumentHelper::persistPdfTextFields($validated, QuotePdfTemplate::getDefault());

        $quote = Quote::create([
            'client_id' => $validated['client_id'],
            'consecutive' => $validated['consecutive'],
            'date' => $validated['date'],
            'currency' => $validated['currency'],
            'exchange_rate' => $validated['exchange_rate'] ?? null,
            'show_prev_license_column' => ! empty($validated['show_prev_license_column']),
            'show_raa_column' => ! empty($validated['show_raa_column']),
            'show_service_type_column' => ! empty($validated['show_service_type_column']),
            'show_description_column' => array_key_exists('show_description_column', $validated) ? ! empty($validated['show_description_column']) : true,
            'show_row_id_column' => ! empty($validated['show_row_id_column']),
            'show_franquicia_column' => ! empty($validated['show_franquicia_column']),
            'show_centro_costos_column' => ! empty($validated['show_centro_costos_column']),
            'show_contacto_column' => ! empty($validated['show_contacto_column']),
            'status' => 'Borrador',
            'total_professional_fees' => round($totalFees, 2),
            'total_invima_fees' => round($totalInvima, 2),
            'apply_tax' => ! empty($validated['apply_tax']),
            'tax_percentage' => isset($validated['tax_percentage']) ? round((float) $validated['tax_percentage'], 2) : null,
            'apply_bank_fee' => ! empty($validated['apply_bank_fee']),
            'bank_fee_value' => ! empty($validated['apply_bank_fee']) && isset($validated['bank_fee_value']) ? round((float) $validated['bank_fee_value'], 2) : null,
            'pdf_body_html' => $pdfFields['pdf_body_html'],
            'pdf_side_note_html' => $pdfFields['pdf_side_note_html'],
            'pdf_footer' => $pdfFields['pdf_footer'],
        ]);

        foreach ($validated['items'] as $pos => $row) {
            // Resolver o crear el tipo de trámite a partir del texto libre (opcional).
            $serviceTypeName = trim($row['service_type_name'] ?? '');
            if ($serviceTypeName === '') {
                $serviceTypeName = 'Sin trámite especificado';
            }
            $serviceType = ServiceType::firstOrCreate(
                ['name' => $serviceTypeName],
                ['is_active' => true]
            );

            QuoteItem::create([
                'quote_id' => $quote->id,
                'service_id' => $row['service_id'] ?? null,
                'service_label' => isset($row['service_label']) && trim((string) $row['service_label']) !== '' ? trim((string) $row['service_label']) : null,
                'item_position' => (int) ($row['item_position'] ?? $pos + 1),
                'row_id' => isset($row['row_id']) && trim((string) $row['row_id']) !== '' ? trim((string) $row['row_id']) : null,
                'service_type_id' => $serviceType->id,
                'raa_code' => $row['raa_code'] ?? null,
                'franquicia' => isset($row['franquicia']) && trim((string) $row['franquicia']) !== '' ? trim((string) $row['franquicia']) : null,
                'centro_costos' => isset($row['centro_costos']) && trim((string) $row['centro_costos']) !== '' ? trim((string) $row['centro_costos']) : null,
                'contacto' => isset($row['contacto']) && trim((string) $row['contacto']) !== '' ? trim((string) $row['contacto']) : null,
                'previous_license' => $row['previous_license'] ?? null,
                'description' => $row['description'] ?? null,
                'scope' => $row['scope'] ?? null,
                'fee_value' => (float) ($row['fee_value'] ?? 0),
                'invima_rate_code' => $row['invima_rate_code'] ?? null,
                'invima_rate_value' => (float) ($row['invima_rate_value'] ?? 0),
            ]);
        }

        return redirect()
            ->route('admin.quotes.index')
            ->with('success', 'Cotización creada correctamente.');
    }
}
