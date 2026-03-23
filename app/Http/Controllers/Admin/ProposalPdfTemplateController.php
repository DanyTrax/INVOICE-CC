<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProposalPdfTemplate;
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
        $validated = $request->validate([
            'name' => 'required|string|max:128',
            'logo' => 'nullable|file|max:2048',
            'header_company_name' => 'nullable|string|max:255',
            'header_nit' => 'nullable|string|max:64',
            'header_subtitle' => 'nullable|string|max:500',
            'body_html' => 'nullable|string',
            'footer_text' => 'nullable|string|max:500',
            'signature_name' => 'nullable|string|max:128',
            'signature_position' => 'nullable|string|max:128',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $ext = strtolower($request->file('logo')->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true)) {
                return redirect()->back()->withInput()->withErrors(['logo' => 'Formato de imagen no válido.']);
            }
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'proposal-pdf-logo-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('uploads/proposal-pdf');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file->move($dir, $filename);
            $logoPath = 'uploads/proposal-pdf/' . $filename;
        }

        if (!empty($validated['is_default'])) {
            ProposalPdfTemplate::query()->update(['is_default' => false]);
        }

        ProposalPdfTemplate::create([
            'name' => $validated['name'],
            'logo_path' => $logoPath,
            'header_company_name' => $validated['header_company_name'] ?? null,
            'header_nit' => $validated['header_nit'] ?? null,
            'header_subtitle' => $validated['header_subtitle'] ?? null,
            'body_html' => $validated['body_html'] ?? null,
            'footer_text' => $validated['footer_text'] ?? null,
            'signature_name' => $validated['signature_name'] ?? null,
            'signature_position' => $validated['signature_position'] ?? null,
            'is_default' => !empty($validated['is_default']),
        ]);

        return redirect()
            ->route('admin.settings.section', 'proposal-pdf')
            ->with('success', 'Plantilla de propuesta creada correctamente.');
    }

    public function edit(ProposalPdfTemplate $proposalPdfTemplate): View
    {
        return view('admin.proposal-pdf-templates.edit', ['template' => $proposalPdfTemplate]);
    }

    public function update(Request $request, ProposalPdfTemplate $proposalPdfTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:128',
            'logo' => 'nullable|file|max:2048',
            'header_company_name' => 'nullable|string|max:255',
            'header_nit' => 'nullable|string|max:64',
            'header_subtitle' => 'nullable|string|max:500',
            'body_html' => 'nullable|string',
            'footer_text' => 'nullable|string|max:500',
            'signature_name' => 'nullable|string|max:128',
            'signature_position' => 'nullable|string|max:128',
            'is_default' => 'nullable|boolean',
            'remove_logo' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $ext = strtolower($request->file('logo')->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true)) {
                return redirect()->back()->withInput()->withErrors(['logo' => 'Formato de imagen no válido.']);
            }
        }

        $logoPath = $proposalPdfTemplate->logo_path;
        if (!empty($validated['remove_logo']) && $logoPath) {
            $full = public_path($logoPath);
            if (file_exists($full)) {
                unlink($full);
            }
            $logoPath = null;
        } elseif ($request->hasFile('logo')) {
            if ($proposalPdfTemplate->logo_path) {
                $old = public_path($proposalPdfTemplate->logo_path);
                if (file_exists($old)) {
                    unlink($old);
                }
            }
            $file = $request->file('logo');
            $filename = 'proposal-pdf-logo-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('uploads/proposal-pdf');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file->move($dir, $filename);
            $logoPath = 'uploads/proposal-pdf/' . $filename;
        }

        if (!empty($validated['is_default'])) {
            ProposalPdfTemplate::where('id', '!=', $proposalPdfTemplate->id)->update(['is_default' => false]);
        }

        $proposalPdfTemplate->update([
            'name' => $validated['name'],
            'logo_path' => $logoPath,
            'header_company_name' => $validated['header_company_name'] ?? null,
            'header_nit' => $validated['header_nit'] ?? null,
            'header_subtitle' => $validated['header_subtitle'] ?? null,
            'body_html' => $validated['body_html'] ?? null,
            'footer_text' => $validated['footer_text'] ?? null,
            'signature_name' => $validated['signature_name'] ?? null,
            'signature_position' => $validated['signature_position'] ?? null,
            'is_default' => !empty($validated['is_default']),
        ]);

        return redirect()
            ->route('admin.settings.section', 'proposal-pdf')
            ->with('success', 'Plantilla actualizada.');
    }

    public function destroy(ProposalPdfTemplate $proposalPdfTemplate): RedirectResponse
    {
        if ($proposalPdfTemplate->logo_path) {
            $full = public_path($proposalPdfTemplate->logo_path);
            if (file_exists($full)) {
                unlink($full);
            }
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
