<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UploadHelper
{
    public static function maxProcessDocumentBytes(): int
    {
        return 10240 * 1024;
    }

    public static function processUploadTempDir(): string
    {
        return storage_path('app/temp');
    }

    /**
     * Asegura que storage/app/temp exista y sea escribible por la app.
     */
    public static function ensureProcessUploadTempDir(): void
    {
        $dir = self::processUploadTempDir();
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException(
                'No se pudo crear storage/app/temp. En el servidor ejecute: mkdir -p storage/app/temp && chmod -R 775 storage'
            );
        }

        if (! is_writable($dir)) {
            throw new \RuntimeException(
                'storage/app/temp no tiene permisos de escritura. Ejecute: chmod -R 775 storage && chown -R www-data:www-data storage (o el usuario del servidor web).'
            );
        }

        $probe = $dir.'/.write_probe_'.uniqid('', true);
        if (@file_put_contents($probe, 'ok') === false) {
            throw new \RuntimeException('No se puede escribir en storage/app/temp.');
        }
        @unlink($probe);
    }

    public static function fileUploadErrorMessage(int $phpUploadError): string
    {
        $message = match ($phpUploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido por el servidor (upload_max_filesize / post_max_size en PHP). Reduzca el archivo o pida al administrador aumentar esos límites.',
            UPLOAD_ERR_PARTIAL => 'La subida del archivo se interrumpió. Intente de nuevo.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'El servidor no pudo guardar el archivo en la carpeta temporal de PHP.',
            default => 'No se pudo subir el archivo. Verifique que no supere 10 MB y que el formato sea válido.',
        };

        if (in_array($phpUploadError, [UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION], true)) {
            $phpTmp = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
            $appTmp = self::processUploadTempDir();
            $message .= ' Revise permisos de '.$phpTmp
                .(is_writable($phpTmp) ? '' : ' (sin escritura)')
                .' y de storage/app/temp'
                .(is_dir($appTmp) && is_writable($appTmp) ? '' : ' (sin escritura)')
                .'. En el servidor: chmod -R 775 storage && configure upload_tmp_dir en PHP apuntando a storage/app/temp.';
        }

        return $message;
    }

    public static function logUploadFailure(string $fileName, int $phpUploadError): void
    {
        $phpTmp = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
        Log::warning('Fallo al recibir archivo subido', [
            'file' => $fileName,
            'php_upload_error' => $phpUploadError,
            'upload_tmp_dir' => $phpTmp,
            'upload_tmp_writable' => is_writable($phpTmp),
            'app_temp_dir' => self::processUploadTempDir(),
            'app_temp_writable' => is_dir(self::processUploadTempDir()) && is_writable(self::processUploadTempDir()),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ]);
    }

    public static function postPayloadTooLargeMessage(Request $request, bool $expectsPayload): ?string
    {
        $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);
        if ($contentLength <= 0) {
            return null;
        }

        $postMax = ini_get('post_max_size') ?: '8M';
        $postMaxBytes = self::iniSizeToBytes($postMax);
        if ($contentLength <= $postMaxBytes) {
            return null;
        }

        if (! $expectsPayload) {
            return null;
        }

        return 'El envío supera el límite del servidor (post_max_size='.$postMax.'). Suba menos archivos a la vez o pida al administrador aumentar post_max_size a al menos 32M.';
    }

    /**
     * @param  list<array{name?: mixed, mime?: mixed, content?: mixed}>  $payload
     * @return array{
     *     files: list<array{path: string, name: string, mime: string}>,
     *     errors: list<string>
     * }
     */
    public static function materializeBase64PayloadUploads(array $payload): array
    {
        $files = [];
        $errors = [];
        $maxBytes = self::maxProcessDocumentBytes();

        foreach ($payload as $i => $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            $mime = trim((string) ($item['mime'] ?? 'application/octet-stream')) ?: 'application/octet-stream';
            $content = (string) ($item['content'] ?? '');
            $label = $name !== '' ? $name : 'Archivo '.($i + 1);

            if ($name === '' || $content === '') {
                $errors[] = $label.': datos incompletos.';

                continue;
            }

            if (preg_match('/^data:.*?;base64,(.*)$/s', $content, $matches)) {
                $content = $matches[1];
            }

            $binary = base64_decode($content, true);
            if ($binary === false) {
                $errors[] = $label.': contenido no válido.';

                continue;
            }

            if ($binary === '') {
                $errors[] = $label.': archivo vacío.';

                continue;
            }

            if (strlen($binary) > $maxBytes) {
                $errors[] = $label.': supera el tamaño máximo de 10 MB.';

                continue;
            }

            try {
                self::ensureProcessUploadTempDir();
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();

                continue;
            }

            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $baseName = pathinfo($name, PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\pL]/u', '_', $baseName) ?: 'file';
            $uniqueName = Str::uuid().'_'.$safeName.($extension ? '.'.$extension : '');
            $fullPath = self::processUploadTempDir().'/'.$uniqueName;

            if (@file_put_contents($fullPath, $binary) === false) {
                $errors[] = $label.': no se pudo guardar en storage/app/temp.';

                continue;
            }

            $files[] = [
                'path' => $fullPath,
                'name' => $name,
                'mime' => $mime,
            ];
        }

        return ['files' => $files, 'errors' => $errors];
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
            default => $number,
        };
    }
}
