<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Throwable;

class GitWorkingCopyService
{
    /**
     * Información del commit actual del working copy (misma carpeta que el despliegue).
     *
     * @return array{available: bool, short_hash: ?string, full_hash: ?string, commit_at: ?string, branch: ?string, subject: ?string, error: ?string}
     */
    public function getInfo(): array
    {
        $base = base_path();
        if (! is_dir($base.'/.git')) {
            return [
                'available' => false,
                'short_hash' => null,
                'full_hash' => null,
                'commit_at' => null,
                'branch' => null,
                'subject' => null,
                'error' => 'No hay carpeta .git (código no desplegado desde Git o copia sin historial).',
            ];
        }

        try {
            $run = fn (array $args) => Process::path($base)->run(array_merge(['git'], $args));

            $rShort = $run(['rev-parse', '--short', 'HEAD']);
            if (! $rShort->successful()) {
                return [
                    'available' => false,
                    'short_hash' => null,
                    'full_hash' => null,
                    'commit_at' => null,
                    'branch' => null,
                    'subject' => null,
                    'error' => 'Git no está disponible o falló: '.trim($rShort->errorOutput() ?: $rShort->output()),
                ];
            }

            $short = trim($rShort->output());
            $full = trim($run(['rev-parse', 'HEAD'])->output());
            $branch = trim($run(['rev-parse', '--abbrev-ref', 'HEAD'])->output());
            $dateRaw = trim($run(['log', '-1', '--format=%cI'])->output());
            $subject = trim($run(['log', '-1', '--format=%s'])->output());

            if ($short === '' || $full === '') {
                return [
                    'available' => false,
                    'short_hash' => null,
                    'full_hash' => null,
                    'commit_at' => null,
                    'branch' => null,
                    'subject' => null,
                    'error' => 'Git no devolvió el commit actual (revisa permisos de ejecución).',
                ];
            }

            return [
                'available' => true,
                'short_hash' => $short,
                'full_hash' => $full,
                'commit_at' => $dateRaw !== '' ? $dateRaw : null,
                'branch' => $branch !== '' ? $branch : null,
                'subject' => $subject !== '' ? $subject : null,
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'available' => false,
                'short_hash' => null,
                'full_hash' => null,
                'commit_at' => null,
                'branch' => null,
                'subject' => null,
                'error' => 'No se pudo leer Git: '.$e->getMessage(),
            ];
        }
    }
}
