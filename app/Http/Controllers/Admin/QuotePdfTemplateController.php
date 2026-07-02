<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuotePdfTemplate;
use App\Support\PdfDocumentHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuotePdfTemplateController extends Controller
{
    public function index(): View
    {
        $templates = QuotePdfTemplate::orderByRaw('is_default DESC')
            ->orderBy('name')
            ->get();

        return view('admin.quote-pdf-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.quote-pdf-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = PdfDocumentHelper::validateTemplateRequest($request);

        $upload = PdfDocumentHelper::processLetterheadUpload($request, null, 'quote-pdf-letterhead', 'quote-pdf');
        if ($upload['error']) {
            return redirect()->back()->withInput()->withErrors(['letterhead' => $upload['error']]);
        }

        if (! empty($validated['is_default'])) {
            QuotePdfTemplate::where('id', '>', 0)->update(['is_default' => false]);
        }

        QuotePdfTemplate::create(array_merge(PdfDocumentHelper::templatePayload($validated), [
            'letterhead_path' => $upload['path'],
            'letterhead_drive_id' => $upload['drive_id'],
        ]));

        return PdfDocumentHelper::appendLetterheadDriveFlashMessage(
            redirect()->route('admin.settings.section', 'quote-pdf'),
            $upload,
            'Plantilla creada correctamente.'
        );
    }

    public function edit(QuotePdfTemplate $quotePdfTemplate): View
    {
        return view('admin.quote-pdf-templates.edit', ['template' => $quotePdfTemplate]);
    }

    /**
     * Duplicar una plantilla con todos sus datos para editar solo lo básico.
     */
    public function duplicate(QuotePdfTemplate $quotePdfTemplate): RedirectResponse
    {
        $copy = PdfDocumentHelper::duplicateTemplate($quotePdfTemplate, QuotePdfTemplate::class, 'quote-pdf');

        return redirect()
            ->route('admin.settings.quote-pdf-templates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Edita lo que necesites y guarda.');
    }

    public function update(Request $request, QuotePdfTemplate $quotePdfTemplate): RedirectResponse
    {
        $validated = PdfDocumentHelper::validateTemplateRequest($request);

        $current = $quotePdfTemplate->letterhead_path ?: $quotePdfTemplate->logo_path;
        $upload = PdfDocumentHelper::processLetterheadUpload(
            $request,
            $current,
            'quote-pdf-letterhead',
            'quote-pdf',
            $quotePdfTemplate->letterhead_drive_id
        );
        if ($upload['error']) {
            return redirect()->back()->withInput()->withErrors(['letterhead' => $upload['error']]);
        }

        if (! empty($validated['is_default'])) {
            QuotePdfTemplate::where('id', '!=', $quotePdfTemplate->id)->update(['is_default' => false]);
        }

        $quotePdfTemplate->update(array_merge(PdfDocumentHelper::templatePayload($validated), [
            'letterhead_path' => $upload['path'],
            'letterhead_drive_id' => $upload['drive_id'],
        ]));

        return PdfDocumentHelper::appendLetterheadDriveFlashMessage(
            redirect()->route('admin.settings.section', 'quote-pdf'),
            $upload,
            'Plantilla actualizada.'
        );
    }

    public function destroy(QuotePdfTemplate $quotePdfTemplate): RedirectResponse
    {
        foreach ([$quotePdfTemplate->letterhead_path, $quotePdfTemplate->logo_path] as $path) {
            if ($path) {
                $full = public_path($path);
                if (file_exists($full)) {
                    @unlink($full);
                }
            }
        }
        if ($quotePdfTemplate->letterhead_drive_id) {
            PdfDocumentHelper::deleteLetterheadFromDrive($quotePdfTemplate->letterhead_drive_id);
        }
        $wasDefault = $quotePdfTemplate->is_default;
        $quotePdfTemplate->delete();
        if ($wasDefault) {
            $first = QuotePdfTemplate::orderBy('id')->first();
            if ($first) {
                $first->update(['is_default' => true]);
            }
        }

        return redirect()
            ->route('admin.settings.section', 'quote-pdf')
            ->with('success', 'Plantilla eliminada.');
    }
}
