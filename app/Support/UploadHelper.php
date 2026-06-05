<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class UploadHelper
{
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
}
