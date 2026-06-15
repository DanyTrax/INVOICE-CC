<?php

use App\Support\UploadHelper;
use App\Models\Process;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rams:setup-upload', function () {
    $tempDir = UploadHelper::processUploadTempDir();

    if (! is_dir($tempDir) && ! @mkdir($tempDir, 0775, true) && ! is_dir($tempDir)) {
        $this->error('No se pudo crear '.$tempDir);

        return 1;
    }

    $userIniPath = public_path('.user.ini');
    $userIni = "upload_tmp_dir = {$tempDir}\n"
        ."upload_max_filesize = 32M\n"
        ."post_max_size = 48M\n"
        ."max_execution_time = 300\n";

    if (@file_put_contents($userIniPath, $userIni) === false) {
        $this->warn('No se pudo escribir public/.user.ini. Créelo manualmente con:');
        $this->line($userIni);
    } else {
        $this->info('Creado public/.user.ini');
    }

    $phpTmp = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
    $this->line('storage/app/temp: '.(is_writable($tempDir) ? 'escribible' : 'SIN escritura'));
    $this->line('PHP upload_tmp_dir actual: '.$phpTmp.' ('.(is_writable($phpTmp) ? 'escribible' : 'SIN escritura').')');
    $this->line('post_max_size: '.ini_get('post_max_size'));
    $this->line('Ejecute en el servidor: chmod -R 775 storage bootstrap/cache');

    return 0;
})->purpose('Prepara storage/app/temp y .user.ini para subidas en hosting compartido');

Artisan::command('rams:sync-process-drive-folders {--id= : ID de una solicitud específica}', function () {
    $drive = app(GoogleDriveService::class);
    $query = Process::query()->with(['client', 'serviceType', 'quoteItem.serviceType']);

    if ($this->option('id')) {
        $query->whereKey((int) $this->option('id'));
    }

    $processes = $query->orderBy('id')->get();
    if ($processes->isEmpty()) {
        $this->warn('No hay solicitudes para sincronizar.');

        return 0;
    }

    $created = 0;
    $synced = 0;
    $failed = 0;

    foreach ($processes as $process) {
        $hadFolder = ! empty($process->drive_folder_id);
        $targetName = $process->driveFolderName();

        try {
            $drive->syncProcessDriveFolder($process);
            if ($hadFolder) {
                $synced++;
                $this->line("✓ {$process->displayReference()} → {$targetName}");
            } else {
                $created++;
                $this->line("+ {$process->displayReference()} → carpeta creada ({$targetName})");
            }
        } catch (\Throwable $e) {
            $failed++;
            $this->error("✗ {$process->displayReference()}: ".$e->getMessage());
        }
    }

    $this->newLine();
    $this->info("Listo: {$created} creadas, {$synced} actualizadas, {$failed} con error.");

    return $failed > 0 ? 1 : 0;
})->purpose('Crea o renombra carpetas Drive de solicitudes con código (siglas) y estructura País → Empresa → Solicitud');

Artisan::command('rams:sync-drive-prune {--execute : Eliminar carpetas huérfanas (sin esto solo muestra vista previa)} {--sync-first : Sincronizar carpetas de solicitudes antes de revisar}', function () {
    $execute = (bool) $this->option('execute');
    $service = app(\App\Services\DriveStructureCleanupService::class);

    if ($this->option('sync-first')) {
        $this->info('Sincronizando carpetas de solicitudes…');
        $exit = Artisan::call('rams:sync-process-drive-folders');
        $this->line(trim(Artisan::output()));
        if ($exit !== 0) {
            $this->warn('La sincronización de solicitudes reportó errores; continúo con la revisión.');
        }
        $this->newLine();
    }

    $this->info($execute
        ? 'Eliminando carpetas huérfanas en Google Drive…'
        : 'Vista previa: carpetas huérfanas en Google Drive (use --execute para eliminar)');

    try {
        $result = $service->pruneOrphanFolders($execute);
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return 1;
    }

    $kept = $result['kept'];
    $this->line('Carpeta base: '.$result['base_folder_id']);
    $this->line('Carpetas protegidas en árbol: '.$result['protected_count']);
    $this->newLine();
    $this->comment('Se conserva lo registrado en la app:');
    $this->line('  · Empresas: '.$kept['companies']);
    $this->line('  · Solicitudes (procesos): '.$kept['processes']);
    $this->line('  · Registros antiguos: '.$kept['registrations']);
    $this->line('  · Archivos de documentos: '.$kept['document_files']);
    $this->line('  · Capacitaciones: '.$kept['capacitaciones']);
    $this->line('  · Backups RAMS y RAMS Membretes PDF (completos)');
    $this->newLine();
    $this->comment('Se eliminará cualquier otra carpeta bajo la base (países sin empresa, pruebas, solicitudes borradas del sistema), aunque tenga archivos.');

    if ($result['orphan_folders'] === []) {
        $this->info('No hay carpetas huérfanas bajo la carpeta base.');

        return 0;
    }

    $this->warn('Carpetas huérfanas encontradas: '.count($result['orphan_folders']));
    foreach ($result['orphan_folders'] as $orphan) {
        $this->line('  · '.$orphan['path']);
    }

    if (! $execute) {
        $this->newLine();
        $this->comment('Ejecute con --execute para enviar estas carpetas a la papelera de Drive.');
        $this->comment('Recomendado antes: php artisan rams:sync-drive-prune --sync-first');

        return 0;
    }

    if ($result['errors'] !== []) {
        $this->newLine();
        $this->error('Errores ('.count($result['errors']).'):');
        foreach ($result['errors'] as $error) {
            $this->line('  '.$error);
        }
    }

    $this->newLine();
    $this->info('Eliminadas: '.count($result['deleted']).' · Errores: '.count($result['errors']));

    return $result['errors'] !== [] ? 1 : 0;
})->purpose('Limpia Drive: conserva empresas, solicitudes y documentos de la app; elimina el resto (pruebas, países huérfanos)');
