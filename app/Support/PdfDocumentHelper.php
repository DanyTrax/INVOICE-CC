<?php

namespace App\Support;

use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

        $driveId = $template->letterhead_drive_id ?? null;
        if ($driveId) {
            try {
                return self::restoreLetterheadFromDrive($template, $driveId);
            } catch (\Throwable $e) {
                Log::warning('No se pudo restaurar membrete desde Google Drive', [
                    'template_id' => $template->id ?? null,
                    'drive_id' => $driveId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Ruta relativa (public/) del membrete, restaurando desde Drive si hace falta.
     */
    public static function resolveLetterheadRelativePath(?object $template): ?string
    {
        $absolute = self::resolveLetterheadPath($template);
        if (! $absolute) {
            return null;
        }

        $publicRoot = rtrim(public_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if (str_starts_with($absolute, $publicRoot)) {
            return ltrim(str_replace('\\', '/', substr($absolute, strlen($publicRoot))), '/');
        }

        return null;
    }

    protected static function restoreLetterheadFromDrive(object $template, string $driveId): string
    {
        $drive = app(GoogleDriveService::class);
        $info = $drive->getFileInfo($driveId);
        $content = $drive->downloadFile($driveId);

        $uploadSubdir = self::letterheadUploadSubdirForTemplate($template);
        $name = $info['name'] ?? 'letterhead.png';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: 'png');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'png';
        }

        $filename = 'letterhead-drive-'.time().'-'.uniqid().'.'.$ext;
        $relativePath = 'uploads/'.$uploadSubdir.'/'.$filename;
        $dir = public_path('uploads/'.$uploadSubdir);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fullPath = public_path($relativePath);
        file_put_contents($fullPath, $content);

        if ($template instanceof Model && $template->exists) {
            $template->update(['letterhead_path' => $relativePath]);
        }

        return $fullPath;
    }

    protected static function letterheadUploadSubdirForTemplate(object $template): string
    {
        $path = (string) ($template->letterhead_path ?? $template->logo_path ?? '');
        if (str_contains($path, 'proposal-pdf')) {
            return 'proposal-pdf';
        }

        return 'quote-pdf';
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
     * @return array{path: ?string, drive_id: ?string, error: ?string, drive_warning: ?string}
     */
    public static function processLetterheadUpload(
        \Illuminate\Http\Request $request,
        ?string $currentPath,
        string $filenamePrefix,
        string $uploadSubdir,
        ?string $currentDriveId = null
    ): array {
        if ($request->boolean('remove_letterhead')) {
            if ($currentPath) {
                $full = public_path($currentPath);
                if (file_exists($full)) {
                    @unlink($full);
                }
            }
            self::deleteLetterheadFromDrive($currentDriveId);

            return ['path' => null, 'drive_id' => null, 'error' => null, 'drive_warning' => null];
        }

        if (! $request->hasFile('letterhead')) {
            $driveId = $currentDriveId;
            $driveWarning = null;
            if (! $driveId && $currentPath && file_exists(public_path($currentPath))) {
                $driveResult = self::uploadLetterheadToDrive(
                    public_path($currentPath),
                    basename($currentPath),
                    $uploadSubdir
                );
                $driveId = $driveResult['drive_id'];
                $driveWarning = $driveResult['warning'];
            }

            return [
                'path' => $currentPath,
                'drive_id' => $driveId,
                'error' => null,
                'drive_warning' => $driveWarning,
            ];
        }

        $ext = strtolower($request->file('letterhead')->getClientOriginalExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true)) {
            return [
                'path' => $currentPath,
                'drive_id' => $currentDriveId,
                'error' => 'Formato de imagen no válido.',
                'drive_warning' => null,
            ];
        }

        if ($currentPath) {
            $old = public_path($currentPath);
            if (file_exists($old)) {
                @unlink($old);
            }
        }
        self::deleteLetterheadFromDrive($currentDriveId);

        $file = $request->file('letterhead');
        $filename = $filenamePrefix.'-'.time().'-'.uniqid().'.'.$ext;
        $dir = public_path('uploads/'.$uploadSubdir);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file->move($dir, $filename);
        $relativePath = 'uploads/'.$uploadSubdir.'/'.$filename;

        $driveResult = self::uploadLetterheadToDrive(public_path($relativePath), $filename, $uploadSubdir);

        return [
            'path' => $relativePath,
            'drive_id' => $driveResult['drive_id'],
            'error' => null,
            'drive_warning' => $driveResult['warning'],
        ];
    }

    /**
     * @return array{drive_id: ?string, warning: ?string}
     */
    protected static function uploadLetterheadToDrive(string $fullPath, string $originalName, string $uploadSubdir): array
    {
        try {
            $drive = app(GoogleDriveService::class);
            $folderId = $drive->getOrCreatePdfLetterheadsFolder($uploadSubdir);
            $mime = mime_content_type($fullPath) ?: 'image/png';
            $driveFile = $drive->uploadFile($fullPath, $originalName, $folderId, $mime);

            return ['drive_id' => $driveFile['id'] ?? null, 'warning' => null];
        } catch (\Throwable $e) {
            Log::warning('Membrete guardado en servidor pero no en Google Drive', [
                'file' => $originalName,
                'error' => $e->getMessage(),
            ]);

            return [
                'drive_id' => null,
                'warning' => 'El membrete se guardó en el servidor, pero no en Google Drive: '.$e->getMessage(),
            ];
        }
    }

    public static function deleteLetterheadFromDrive(?string $driveId): void
    {
        if (! $driveId) {
            return;
        }

        try {
            app(GoogleDriveService::class)->deleteFileOrFolder($driveId);
        } catch (\Throwable $e) {
            Log::warning('No se pudo eliminar membrete anterior en Drive', [
                'drive_id' => $driveId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array{drive_id?: ?string, drive_warning?: ?string}  $upload
     */
    public static function appendLetterheadDriveFlashMessage(
        \Illuminate\Http\RedirectResponse $redirect,
        array $upload,
        string $successMessage
    ): \Illuminate\Http\RedirectResponse {
        if (! empty($upload['drive_id']) && empty($upload['drive_warning'])) {
            $successMessage .= ' Membrete respaldado en Google Drive.';
        }

        $redirect->with('success', $successMessage);

        if (! empty($upload['drive_warning'])) {
            $redirect->with('error', $upload['drive_warning']);
        }

        return $redirect;
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
            'signature_name_font_size' => 'nullable|integer|min:4|max:24',
            'signature_position_font_size' => 'nullable|integer|min:4|max:24',
            'signature_margin_top_px' => 'nullable|integer|min:0|max:400',
            'letterhead_footer_reserve_mm' => 'nullable|integer|min:20|max:80',
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
            'signature_position_font_size' => (int) ($validated['signature_position_font_size'] ?? 9),
            'signature_margin_top_px' => (int) ($validated['signature_margin_top_px'] ?? 130),
            'letterhead_footer_reserve_mm' => (int) ($validated['letterhead_footer_reserve_mm'] ?? 42),
            'is_default' => ! empty($validated['is_default']),
        ];
    }
}
