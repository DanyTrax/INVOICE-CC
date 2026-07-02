<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProposalPdfTemplate;
use App\Support\PdfDocumentHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProposalPdfTemplateController extends Controller
{
    public function create(): View
    {
        return view('admin.proposal-pdf-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = PdfDocumentHelper::validateTemplateRequest($request);

        $upload = PdfDocumentHelper::processLetterheadUpload($request, null, 'proposal-pdf-letterhead', 'proposal-pdf');
        if ($upload['error']) {
            return redirect()->back()->withInput()->withErrors(['letterhead' => $upload['error']]);
        }

        $signature = PdfDocumentHelper::processSignatureUpload($request, null, 'proposal-pdf');
        if ($signature['error']) {
            return redirect()->back()->withInput()->withErrors(['signature_image' => $signature['error']]);
        }

        if (! empty($validated['is_default'])) {
            ProposalPdfTemplate::query()->update(['is_default' => false]);
        }

        ProposalPdfTemplate::create(array_merge(PdfDocumentHelper::templatePayload($validated), [
            'letterhead_path' => $upload['path'],
            'letterhead_drive_id' => $upload['drive_id'],
            'signature_image_path' => $signature['path'],
            'signature_image_drive_id' => $signature['drive_id'],
        ]));

        return PdfDocumentHelper::appendLetterheadDriveFlashMessage(
            redirect()->route('admin.settings.section', 'proposal-pdf'),
            $upload,
            'Plantilla de propuesta creada correctamente.'
        );
    }

    public function edit(ProposalPdfTemplate $proposalPdfTemplate): View
    {
        return view('admin.proposal-pdf-templates.edit', ['template' => $proposalPdfTemplate]);
    }

    /**
     * Duplicar una plantilla con todos sus datos para editar solo lo básico.
     */
    public function duplicate(ProposalPdfTemplate $proposalPdfTemplate): RedirectResponse
    {
        $copy = PdfDocumentHelper::duplicateTemplate($proposalPdfTemplate, ProposalPdfTemplate::class, 'proposal-pdf');

        return redirect()
            ->route('admin.settings.proposal-pdf-templates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Edita lo que necesites y guarda.');
    }

    public function update(Request $request, ProposalPdfTemplate $proposalPdfTemplate): RedirectResponse
    {
        $validated = PdfDocumentHelper::validateTemplateRequest($request);

        $current = $proposalPdfTemplate->letterhead_path ?: $proposalPdfTemplate->logo_path;
        $upload = PdfDocumentHelper::processLetterheadUpload(
            $request,
            $current,
            'proposal-pdf-letterhead',
            'proposal-pdf',
            $proposalPdfTemplate->letterhead_drive_id
        );
        if ($upload['error']) {
            return redirect()->back()->withInput()->withErrors(['letterhead' => $upload['error']]);
        }

        $signature = PdfDocumentHelper::processSignatureUpload(
            $request,
            $proposalPdfTemplate->signature_image_path,
            'proposal-pdf',
            $proposalPdfTemplate->signature_image_drive_id
        );
        if ($signature['error']) {
            return redirect()->back()->withInput()->withErrors(['signature_image' => $signature['error']]);
        }

        if (! empty($validated['is_default'])) {
            ProposalPdfTemplate::where('id', '!=', $proposalPdfTemplate->id)->update(['is_default' => false]);
        }

        $proposalPdfTemplate->update(array_merge(PdfDocumentHelper::templatePayload($validated), [
            'letterhead_path' => $upload['path'],
            'letterhead_drive_id' => $upload['drive_id'],
            'signature_image_path' => $signature['path'],
            'signature_image_drive_id' => $signature['drive_id'],
        ]));

        return PdfDocumentHelper::appendLetterheadDriveFlashMessage(
            redirect()->route('admin.settings.section', 'proposal-pdf'),
            $upload,
            'Plantilla actualizada.'
        );
    }

    public function destroy(ProposalPdfTemplate $proposalPdfTemplate): RedirectResponse
    {
        foreach ([$proposalPdfTemplate->letterhead_path, $proposalPdfTemplate->logo_path, $proposalPdfTemplate->signature_image_path] as $path) {
            if ($path) {
                $full = public_path($path);
                if (file_exists($full)) {
                    @unlink($full);
                }
            }
        }
        if ($proposalPdfTemplate->letterhead_drive_id) {
            PdfDocumentHelper::deleteLetterheadFromDrive($proposalPdfTemplate->letterhead_drive_id);
        }
        if ($proposalPdfTemplate->signature_image_drive_id) {
            PdfDocumentHelper::deleteLetterheadFromDrive($proposalPdfTemplate->signature_image_drive_id);
        }
        $wasDefault = $proposalPdfTemplate->is_default;
        $proposalPdfTemplate->delete();
        if ($wasDefault) {
            $first = ProposalPdfTemplate::orderBy('id')->first();
            if ($first) {
                $first->update(['is_default' => true]);
            }
        }

        return redirect()
            ->route('admin.settings.section', 'proposal-pdf')
            ->with('success', 'Plantilla eliminada.');
    }
}
