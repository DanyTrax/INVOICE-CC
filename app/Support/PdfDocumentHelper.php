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
        $companyName = $document->client->name ?? '';

        // Si la cotización tiene una persona de contacto (usuario cliente), {{cliente}}
        // usa su nombre; si no, repite el nombre de la empresa (comportamiento anterior).
        $contactName = null;
        if (method_exists($document, 'contactUser')) {
            $contactName = optional($document->contactUser)->name;
        }
        $cliente = ! empty($contactName) ? $contactName : $companyName;

        $consecutivo = $document->consecutive ?? '';
        $destinatario = $companyName;

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
        return self::restoreImageFromDrive(
            $template,
            $driveId,
            self::letterheadUploadSubdirForTemplate($template),
            'letterhead',
            'letterhead_path'
        );
    }

    /**
     * Restaura una imagen (membrete o firma) desde Drive al almacenamiento público.
     */
    protected static function restoreImageFromDrive(object $template, string $driveId, string $uploadSubdir, string $filenamePrefix, string $pathField): string
    {
        $drive = app(GoogleDriveService::class);
        $info = $drive->getFileInfo($driveId);
        $content = $drive->downloadFile($driveId);

        $name = $info['name'] ?? $filenamePrefix.'.png';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: 'png');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'png';
        }

        $filename = $filenamePrefix.'-drive-'.time().'-'.uniqid().'.'.$ext;
        $relativePath = 'uploads/'.$uploadSubdir.'/'.$filename;
        $dir = public_path('uploads/'.$uploadSubdir);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fullPath = public_path($relativePath);
        file_put_contents($fullPath, $content);

        $mime = mime_content_type($fullPath) ?: '';
        if (! str_starts_with($mime, 'image/') || $mime === 'image/svg+xml') {
            @unlink($fullPath);
            throw new \RuntimeException('El archivo en Drive no es una imagen válida (use JPG o PNG).');
        }

        if ($template instanceof Model && $template->exists) {
            $template->update([$pathField => $relativePath]);
        }

        return $fullPath;
    }

    /**
     * Ruta absoluta de la imagen de firma (restaurando desde Drive si hace falta).
     */
    public static function resolveSignatureImagePath(?object $template): ?string
    {
        if (! $template) {
            return null;
        }

        $path = $template->signature_image_path ?? null;
        if ($path && file_exists(public_path($path))) {
            return public_path($path);
        }

        $driveId = $template->signature_image_drive_id ?? null;
        if ($driveId) {
            try {
                return self::restoreImageFromDrive(
                    $template,
                    $driveId,
                    self::letterheadUploadSubdirForTemplate($template),
                    'signature',
                    'signature_image_path'
                );
            } catch (\Throwable $e) {
                Log::warning('No se pudo restaurar firma desde Google Drive', [
                    'template_id' => $template->id ?? null,
                    'drive_id' => $driveId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
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
     * Ruta absoluta del membrete para DomPDF (evita data URI que rompe imágenes grandes → X).
     */
    public static function resolveLetterheadSrcForPdf(?string $absolutePath): ?string
    {
        if (! $absolutePath || ! is_readable($absolutePath)) {
            return null;
        }

        $real = realpath($absolutePath);
        if (! $real || ! is_file($real)) {
            return null;
        }

        $mime = mime_content_type($real) ?: '';
        if (! str_starts_with($mime, 'image/') || $mime === 'image/svg+xml') {
            return null;
        }

        return str_replace('\\', '/', $real);
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
        return self::processImageUpload(
            $request,
            $currentPath,
            $filenamePrefix,
            $uploadSubdir,
            $currentDriveId,
            'letterhead',
            'remove_letterhead'
        );
    }

    /**
     * Sube/actualiza/elimina la imagen de firma (mismo comportamiento que el membrete).
     *
     * @return array{path: ?string, drive_id: ?string, error: ?string, drive_warning: ?string}
     */
    public static function processSignatureUpload(
        \Illuminate\Http\Request $request,
        ?string $currentPath,
        string $uploadSubdir,
        ?string $currentDriveId = null
    ): array {
        return self::processImageUpload(
            $request,
            $currentPath,
            'signature',
            $uploadSubdir,
            $currentDriveId,
            'signature_image',
            'remove_signature_image'
        );
    }

    /**
     * @return array{path: ?string, drive_id: ?string, error: ?string, drive_warning: ?string}
     */
    protected static function processImageUpload(
        \Illuminate\Http\Request $request,
        ?string $currentPath,
        string $filenamePrefix,
        string $uploadSubdir,
        ?string $currentDriveId,
        string $fileField,
        string $removeField
    ): array {
        if ($request->boolean($removeField)) {
            if ($currentPath) {
                $full = public_path($currentPath);
                if (file_exists($full)) {
                    @unlink($full);
                }
            }
            self::deleteLetterheadFromDrive($currentDriveId);

            return ['path' => null, 'drive_id' => null, 'error' => null, 'drive_warning' => null];
        }

        if (! $request->hasFile($fileField)) {
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

        $file = $request->file($fileField);
        if (! $file->isValid()) {
            return [
                'path' => $currentPath,
                'drive_id' => $currentDriveId,
                'error' => self::letterheadUploadErrorMessage($file->getError()),
                'drive_warning' => null,
            ];
        }

        $ext = strtolower($file->getClientOriginalExtension());
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

    public static function fileUploadErrorMessage(int $phpUploadError): string
    {
        return UploadHelper::fileUploadErrorMessage($phpUploadError);
    }

    public static function letterheadUploadErrorMessage(int $phpUploadError): string
    {
        return UploadHelper::fileUploadErrorMessage($phpUploadError);
    }

    public static function templateValidationMessages(): array
    {
        return [
            'letterhead.file' => 'No se pudo subir el membrete. Use JPG o PNG (máx. 20 MB) o guarde sin cambiar la imagen.',
            'letterhead.max' => 'El membrete es demasiado grande. Tamaño máximo: 20 MB.',
            'letterhead.uploaded' => 'No se pudo subir el membrete (archivo inválido o límite del servidor). Guarde sin cambiar la imagen o reduzca el tamaño del archivo.',
        ];
    }

    public static function templateValidationRules(?\Illuminate\Http\Request $request = null): array
    {
        $rules = [
            'name' => 'required|string|max:128',
            'body_html' => 'nullable|string',
            'side_note_html' => 'nullable|string',
            'closing_footer_html' => 'nullable|string',
            'signature_name' => 'nullable|string|max:128',
            'signature_position' => 'nullable|string|max:128',
            'signature_image_height_px' => 'nullable|integer|min:10|max:250',
            'signature_name_font_size' => 'nullable|integer|min:4|max:24',
            'signature_position_font_size' => 'nullable|integer|min:4|max:24',
            'signature_margin_top_px' => 'nullable|integer|min:0|max:400',
            'letterhead_footer_reserve_mm' => 'nullable|integer|min:20|max:80',
            'doc_title_text' => 'nullable|string|max:128',
            'doc_title_font_size' => 'nullable|integer|min:4|max:40',
            'doc_title_bold' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'remove_letterhead' => 'nullable|boolean',
            'remove_signature_image' => 'nullable|boolean',
        ];

        if ($request && $request->hasFile('letterhead')) {
            $rules['letterhead'] = ['file', 'max:20480'];
        }

        if ($request && $request->hasFile('signature_image')) {
            $rules['signature_image'] = ['file', 'image', 'max:10240'];
        }

        return $rules;
    }

    /**
     * Validación previa cuando el POST supera post_max_size (PHP vacía $_POST).
     */
    public static function postPayloadTooLargeMessage(\Illuminate\Http\Request $request): ?string
    {
        $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);
        if ($contentLength <= 0) {
            return null;
        }

        $postMax = ini_get('post_max_size');
        $postMaxBytes = self::iniSizeToBytes($postMax ?: '8M');
        if ($contentLength > $postMaxBytes && trim((string) $request->input('name', '')) === '') {
            return 'El formulario es demasiado grande para el servidor (post_max_size='.$postMax.'). Guarde sin cambiar el membrete o reduzca el texto en los editores.';
        }

        return null;
    }

    protected static function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function validateTemplateRequest(\Illuminate\Http\Request $request): array
    {
        $tooLarge = self::postPayloadTooLargeMessage($request);
        if ($tooLarge !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'letterhead' => $tooLarge,
            ]);
        }

        return $request->validate(
            self::templateValidationRules($request),
            self::templateValidationMessages()
        );
    }

    /**
     * Duplica una plantilla PDF (cotización o propuesta) copiando todos sus campos y,
     * si existe, el archivo de membrete a un nuevo fichero (no comparte el original).
     * La copia nunca queda como "por defecto".
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $modelClass
     * @return T
     */
    public static function duplicateTemplate(Model $source, string $modelClass, string $uploadSubdir): Model
    {
        $copy = $source->replicate(['is_default', 'letterhead_drive_id', 'signature_image_drive_id']);

        $baseName = (string) ($source->name ?? 'Plantilla');
        $copy->name = mb_substr($baseName.' (copia)', 0, 128);
        $copy->is_default = false;
        $copy->letterhead_drive_id = null;
        $copy->signature_image_drive_id = null;

        // Copiar el archivo de membrete a un nuevo nombre para que borrar una no afecte a la otra.
        $sourcePath = $source->letterhead_path ?: $source->logo_path;
        if ($sourcePath && file_exists(public_path($sourcePath))) {
            $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'png');
            $newRelative = 'uploads/'.$uploadSubdir.'/letterhead-copy-'.time().'-'.uniqid().'.'.$ext;
            $dir = public_path('uploads/'.$uploadSubdir);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            @copy(public_path($sourcePath), public_path($newRelative));
            $copy->letterhead_path = $newRelative;
            $copy->logo_path = null;
        }

        // Copiar la imagen de firma a un nuevo fichero independiente.
        $sourceSignature = $source->signature_image_path ?? null;
        if ($sourceSignature && file_exists(public_path($sourceSignature))) {
            $ext = strtolower(pathinfo($sourceSignature, PATHINFO_EXTENSION) ?: 'png');
            $newRelative = 'uploads/'.$uploadSubdir.'/signature-copy-'.time().'-'.uniqid().'.'.$ext;
            $dir = public_path('uploads/'.$uploadSubdir);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            @copy(public_path($sourceSignature), public_path($newRelative));
            $copy->signature_image_path = $newRelative;
        }

        $copy->save();

        return $copy;
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
            'signature_image_height_px' => (int) ($validated['signature_image_height_px'] ?? 55),
            'signature_name_font_size' => (int) ($validated['signature_name_font_size'] ?? 11),
            'signature_position_font_size' => (int) ($validated['signature_position_font_size'] ?? 9),
            'signature_margin_top_px' => (int) ($validated['signature_margin_top_px'] ?? 130),
            'letterhead_footer_reserve_mm' => (int) ($validated['letterhead_footer_reserve_mm'] ?? 42),
            'doc_title_text' => ($validated['doc_title_text'] ?? null) !== null && trim((string) $validated['doc_title_text']) !== ''
                ? trim((string) $validated['doc_title_text'])
                : null,
            'doc_title_font_size' => (int) ($validated['doc_title_font_size'] ?? 9),
            'doc_title_bold' => ! empty($validated['doc_title_bold']),
            'is_default' => ! empty($validated['is_default']),
        ];
    }
}
