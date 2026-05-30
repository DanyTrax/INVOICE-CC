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

    public static function hasMeaningfulHtml(?string $html): bool
    {
        return self::plainTextFromHtml($html) !== '';
    }

    public static function plainTextFromHtml(?string $html): string
    {
        $decoded = html_entity_decode((string) ($html ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', strip_tags($decoded)) ?? '');
    }

    public static function htmlContentEquals(?string $a, ?string $b): bool
    {
        return self::plainTextFromHtml($a) === self::plainTextFromHtml($b);
    }

    /**
     * Guardar override solo si el usuario escribió algo distinto a la plantilla (evita copia congelada).
     */
    public static function persistFieldOverride(?string $input, ?string $templateDefault): ?string
    {
        if (! self::hasMeaningfulHtml($input)) {
            return null;
        }

        if (self::hasMeaningfulHtml($templateDefault) && self::htmlContentEquals($input, $templateDefault)) {
            return null;
        }

        return trim((string) $input);
    }

    /**
     * Valor para mostrar en el formulario: override del documento o, si no hay, plantilla actual.
     *
     * @return array{value: string, is_override: bool, shows_template: bool}
     */
    public static function resolveFormField(mixed $oldInput, ?string $documentValue, ?string $templateValue): array
    {
        if ($oldInput !== null) {
            return [
                'value' => (string) $oldInput,
                'is_override' => self::hasMeaningfulHtml((string) $oldInput),
                'shows_template' => false,
            ];
        }

        if (self::hasMeaningfulHtml($documentValue)) {
            return [
                'value' => trim((string) $documentValue),
                'is_override' => true,
                'shows_template' => false,
            ];
        }

        return [
            'value' => trim((string) ($templateValue ?? '')),
            'is_override' => false,
            'shows_template' => self::hasMeaningfulHtml($templateValue),
        ];
    }

    /**
     * @return array{pdf_body_html: ?string, pdf_side_note_html: ?string, pdf_footer: ?string}
     */
    public static function persistPdfTextFields(array $validated, ?object $defaultTemplate): array
    {
        $template = $defaultTemplate;

        return [
            'pdf_body_html' => self::persistFieldOverride($validated['pdf_body_html'] ?? null, $template?->body_html ?? null),
            'pdf_side_note_html' => self::persistFieldOverride($validated['pdf_side_note_html'] ?? null, $template?->side_note_html ?? null),
            'pdf_footer' => self::persistFieldOverride($validated['pdf_footer'] ?? null, $template?->closing_footer_html ?? null),
        ];
    }

    public static function persistBodyHtmlOnly(?string $input, ?object $defaultTemplate): ?string
    {
        return self::persistFieldOverride($input, $defaultTemplate?->body_html ?? null);
    }

    /**
     * @return array{pdf_side_note_html: ?string, pdf_footer: ?string}
     */
    public static function persistSideFooterFields(array $validated, ?object $defaultTemplate): array
    {
        $template = $defaultTemplate;

        return [
            'pdf_side_note_html' => self::persistFieldOverride($validated['pdf_side_note_html'] ?? null, $template?->side_note_html ?? null),
            'pdf_footer' => self::persistFieldOverride($validated['pdf_footer'] ?? null, $template?->closing_footer_html ?? null),
        ];
    }

    protected static function resolveHtmlField(?string $documentValue, ?string $templateValue, object $document): string
    {
        if (self::hasMeaningfulHtml($documentValue)) {
            return self::replaceTemplateVariables(trim((string) $documentValue), $document);
        }

        if (self::hasMeaningfulHtml($templateValue)) {
            return self::replaceTemplateVariables(trim((string) $templateValue), $document);
        }

        return '';
    }

    public static function resolveBodyHtml(?object $template, ?object $document): string
    {
        return self::resolveHtmlField(
            $document->pdf_body_html ?? null,
            $template->body_html ?? null,
            $document
        );
    }

    public static function resolveSideNoteHtml(?object $template, ?object $document): string
    {
        return self::resolveHtmlField(
            $document->pdf_side_note_html ?? null,
            $template->side_note_html ?? null,
            $document
        );
    }

    public static function resolveClosingFooterHtml(?object $template, ?object $document): string
    {
        return self::resolveHtmlField(
            $document->pdf_footer ?? null,
            $template->closing_footer_html ?? null,
            $document
        );
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
