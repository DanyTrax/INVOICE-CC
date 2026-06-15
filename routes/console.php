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
