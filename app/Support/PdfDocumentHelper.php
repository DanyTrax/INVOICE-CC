<?php

namespace App\Support;

use Carbon\Carbon;

class PdfDocumentHelper
{
    /**
     * Monto sin decimales. USD y COP se redondean al entero más cercano.
     */
    public static function formatMoney(float $amount, string $currency): string
    {
        $rounded = (int) round($amount);

        return number_format($rounded, 0, ',', '.');
    }

    /**
     * @param  object{date?: mixed, client?: object, consecutive?: string}  $document
     */
    public static function replaceTemplateVariables(string $html, object $document): string
    {
        $fechaTexto = $document->date
            ? Carbon::parse($document->date)->locale('es')->translatedFormat('d \d\e F \d\e Y')
            : '';
        $ciudad = 'Bogotá D. C.';
        $cliente = $document->client->name ?? '';
        $consecutivo = $document->consecutive ?? '';
        $destinatario = $document->client->name ?? '';

        return str_replace(
            ['{{fecha}}', '{{ciudad}}', '{{cliente}}', '{{consecutivo}}', '{{destinatario}}'],
            [$fechaTexto, $ciudad, $cliente, $consecutivo, $destinatario],
            $html
        );
    }

    public static function resolveLetterheadPath(?object $template): ?string
    {
        if (! $template) {
            return null;
        }

        foreach (['letterhead_path', 'logo_path'] as $field) {
            $path = $template->{$field} ?? null;
            if ($path && file_exists(public_path($path))) {
                return public_path($path);
            }
        }

        return null;
    }

    /**
     * Data URI del membrete para DomPDF (fondo a página completa).
     */
    public static function resolveLetterheadDataUri(?string $absolutePath): ?string
    {
        if (! $absolutePath || ! is_readable($absolutePath)) {
            return null;
        }

        $mime = mime_content_type($absolutePath) ?: 'image/png';
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolutePath));
    }

    public static function resolveBodyHtml(?object $template, ?object $document): string
    {
        $raw = trim($document->pdf_body_html ?? '');
        if ($raw === '' && $template) {
            $raw = trim($template->body_html ?? '');
        }

        return $raw !== '' ? self::replaceTemplateVariables($raw, $document) : '';
    }

    public static function resolveSideNoteHtml(?object $template, ?object $document): string
    {
        $raw = trim($document->pdf_side_note_html ?? '');
        if ($raw === '' && $template) {
            $raw = trim($template->side_note_html ?? '');
        }

        return $raw !== '' ? self::replaceTemplateVariables($raw, $document) : '';
    }

    public static function resolveClosingFooterHtml(?object $template, ?object $document): string
    {
        $raw = trim($document->pdf_footer ?? '');
        if ($raw === '' && $template) {
            $raw = trim($template->closing_footer_html ?? '');
        }

        return $raw !== '' ? self::replaceTemplateVariables($raw, $document) : '';
    }

    /**
     * @return array{path: ?string, error: ?string}
     */
    public static function processLetterheadUpload(\Illuminate\Http\Request $request, ?string $currentPath, string $filenamePrefix, string $uploadSubdir): array
    {
        if ($request->boolean('remove_letterhead') && $currentPath) {
            $full = public_path($currentPath);
            if (file_exists($full)) {
                @unlink($full);
            }

            return ['path' => null, 'error' => null];
        }

        if (! $request->hasFile('letterhead')) {
            return ['path' => $currentPath, 'error' => null];
        }

        $ext = strtolower($request->file('letterhead')->getClientOriginalExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true)) {
            return ['path' => $currentPath, 'error' => 'Formato de imagen no válido.'];
        }

        if ($currentPath) {
            $old = public_path($currentPath);
            if (file_exists($old)) {
                @unlink($old);
            }
        }

        $file = $request->file('letterhead');
        $filename = $filenamePrefix.'-'.time().'-'.uniqid().'.'.$ext;
        $dir = public_path('uploads/'.$uploadSubdir);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file->move($dir, $filename);

        return ['path' => 'uploads/'.$uploadSubdir.'/'.$filename, 'error' => null];
    }

    public static function templateValidationRules(): array
    {
        return [
            'name' => 'required|string|max:128',
            'letterhead' => 'nullable|file|max:5120',
            'body_html' => 'nullable|string',
            'side_note_html' => 'nullable|string',
            'closing_footer_html' => 'nullable|string',
            'signature_name' => 'nullable|string|max:128',
            'signature_position' => 'nullable|string|max:128',
            'signature_name_font_size' => 'nullable|integer|min:8|max:24',
            'signature_position_font_size' => 'nullable|integer|min:8|max:24',
            'is_default' => 'nullable|boolean',
            'remove_letterhead' => 'nullable|boolean',
        ];
    }

    public static function templatePayload(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'body_html' => $validated['body_html'] ?? null,
            'side_note_html' => $validated['side_note_html'] ?? null,
            'closing_footer_html' => $validated['closing_footer_html'] ?? null,
            'signature_name' => $validated['signature_name'] ?? null,
            'signature_position' => $validated['signature_position'] ?? null,
            'signature_name_font_size' => (int) ($validated['signature_name_font_size'] ?? 11),
            'signature_position_font_size' => (int) ($validated['signature_position_font_size'] ?? 11),
            'is_default' => ! empty($validated['is_default']),
        ];
    }
}
