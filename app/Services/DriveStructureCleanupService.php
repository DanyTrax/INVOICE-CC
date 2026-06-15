<?php

namespace App\Services;

use App\Models\CapacitacionVideo;
use App\Models\Company;
use App\Models\Document;
use App\Models\Process;
use App\Models\ProcessDocument;
use App\Models\ProposalPdfTemplate;
use App\Models\QuotePdfTemplate;
use App\Models\Registration;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Log;

class DriveStructureCleanupService
{
    /** Carpetas de sistema: se conserva todo su contenido. */
    private const SYSTEM_FOLDER_NAMES = [
        'Backups RAMS',
        'RAMS Membretes PDF',
    ];

    public function __construct(
        protected GoogleDriveService $drive,
        protected GeneralSettings $settings,
    ) {}

    /**
     * @return array{
     *     base_folder_id: string,
     *     protected_count: int,
     *     kept: array{companies: int, processes: int, registrations: int, document_files: int, capacitaciones: int},
     *     orphan_folders: list<array{id: string, name: string, path: string}>,
     *     deleted: list<string>,
     *     errors: list<string>
     * }
     */
    public function pruneOrphanFolders(bool $execute = false): array
    {
        $baseId = trim($this->settings->drive_folder_id);
        if ($baseId === '') {
            throw new \RuntimeException('Configure el ID de carpeta base de Google Drive en Configuración.');
        }

        $kept = [
            'companies' => 0,
            'processes' => 0,
            'registrations' => 0,
            'document_files' => 0,
            'capacitaciones' => 0,
        ];

        $protected = $this->buildProtectedFolderMap($baseId, $kept);
        $allFolders = $this->drive->listDescendantFolders($baseId);
        $pathMap = $this->buildFolderPathMap($baseId, $allFolders);

        $orphans = [];
        foreach ($allFolders as $folderId => $folder) {
            if (isset($protected[$folderId])) {
                continue;
            }
            $orphans[] = [
                'id' => $folderId,
                'name' => $folder['name'],
                'path' => $pathMap[$folderId] ?? $folder['name'],
            ];
        }

        usort($orphans, function (array $a, array $b) use ($allFolders): int {
            return $this->folderDepth($b['id'], $allFolders) <=> $this->folderDepth($a['id'], $allFolders);
        });

        $deleted = [];
        $errors = [];

        if ($execute) {
            foreach ($orphans as $orphan) {
                try {
                    $this->drive->deleteFileOrFolderStrict($orphan['id']);
                    $deleted[] = $orphan['path'];
                    Log::info('Drive prune: carpeta eliminada', $orphan);
                } catch (\Throwable $e) {
                    $errors[] = $orphan['path'].': '.$e->getMessage();
                    Log::warning('Drive prune: no se pudo eliminar carpeta', [
                        'folder' => $orphan,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'base_folder_id' => $baseId,
            'protected_count' => count($protected),
            'kept' => $kept,
            'orphan_folders' => $orphans,
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array{companies: int, processes: int, registrations: int, document_files: int, capacitaciones: int}  $kept
     * @return array<string, true>
     */
    protected function buildProtectedFolderMap(string $baseId, array &$kept): array
    {
        $protected = [$baseId => true];

        foreach (self::SYSTEM_FOLDER_NAMES as $systemName) {
            $systemId = $this->drive->findFolderByNameUnder($baseId, $systemName);
            if ($systemId) {
                $this->markFolderTreeProtected($systemId, $protected);
            }
        }

        $companies = Company::query()->orderBy('id')->get();
        foreach ($companies as $company) {
            $companyFolderId = $this->resolveCompanyFolderId($company, $baseId);
            if ($companyFolderId) {
                $kept['companies']++;
                $this->protectFolderAndAncestors($companyFolderId, $baseId, $protected);
            }
        }

        $processes = Process::query()->with(['client', 'serviceType', 'quoteItem.serviceType'])->orderBy('id')->get();
        foreach ($processes as $process) {
            $processFolderId = $this->resolveProcessFolderId($process, $baseId);
            if ($processFolderId) {
                $kept['processes']++;
                $this->markFolderTreeProtected($processFolderId, $protected);
                $this->protectFolderAndAncestors($processFolderId, $baseId, $protected);
            }
        }

        $registrations = Registration::query()->with('company')->orderBy('id')->get();
        foreach ($registrations as $registration) {
            $folderId = $registration->drive_folder_id
                ?: $this->resolveRegistrationFolderId($registration, $baseId);
            if ($folderId) {
                $kept['registrations']++;
                $this->markFolderTreeProtected($folderId, $protected);
                $this->protectFolderAndAncestors($folderId, $baseId, $protected);
            }
        }

        foreach (ProcessDocument::query()->whereNotNull('drive_id')->pluck('drive_id') as $driveId) {
            $kept['document_files']++;
            $this->protectItemAncestors((string) $driveId, $baseId, $protected);
        }

        foreach (Document::query()->whereNotNull('drive_id')->pluck('drive_id') as $driveId) {
            $kept['document_files']++;
            $this->protectItemAncestors((string) $driveId, $baseId, $protected);
        }

        foreach (CapacitacionVideo::query()->whereNotNull('drive_folder_id')->pluck('drive_folder_id') as $folderId) {
            $kept['capacitaciones']++;
            $this->markFolderTreeProtected((string) $folderId, $protected);
            $this->protectFolderAndAncestors((string) $folderId, $baseId, $protected);
        }

        foreach (CapacitacionVideo::query()->whereNotNull('drive_file_id')->pluck('drive_file_id') as $fileId) {
            $this->protectItemAncestors((string) $fileId, $baseId, $protected);
        }

        foreach (QuotePdfTemplate::query()->whereNotNull('letterhead_drive_id')->pluck('letterhead_drive_id') as $fileId) {
            $this->protectItemAncestors((string) $fileId, $baseId, $protected);
        }

        foreach (ProposalPdfTemplate::query()->whereNotNull('letterhead_drive_id')->pluck('letterhead_drive_id') as $fileId) {
            $this->protectItemAncestors((string) $fileId, $baseId, $protected);
        }

        return $protected;
    }

    protected function resolveCompanyFolderId(Company $company, string $baseId): ?string
    {
        if ($company->drive_folder_id) {
            return $company->drive_folder_id;
        }

        $folderName = $this->companyFolderName($company);
        $country = trim((string) ($company->country ?? ''));

        if ($country !== '') {
            $countryId = $this->drive->findFolderByNameUnder($baseId, $country);
            if (! $countryId) {
                return null;
            }

            return $this->drive->findFolderByNameUnder($countryId, $folderName);
        }

        $clientsName = $this->settings->drive_folder_name_with_client ?: 'Clientes';
        $clientsId = $this->drive->findFolderByNameUnder($baseId, $clientsName);
        if (! $clientsId) {
            return null;
        }

        return $this->drive->findFolderByNameUnder($clientsId, $folderName);
    }

    protected function resolveProcessFolderId(Process $process, string $baseId): ?string
    {
        if ($process->drive_folder_id) {
            return $process->drive_folder_id;
        }

        $company = $process->client;
        if (! $company) {
            return null;
        }

        $companyFolderId = $this->resolveCompanyFolderId($company, $baseId);
        if (! $companyFolderId) {
            return null;
        }

        $expectedName = $process->driveFolderName();
        $found = $this->drive->findFolderByNameUnder($companyFolderId, $expectedName);
        if ($found) {
            return $found;
        }

        $code = $process->displayReference();
        foreach ($this->drive->listImmediateChildren($companyFolderId) as $item) {
            if ($item['mimeType'] !== 'application/vnd.google-apps.folder') {
                continue;
            }
            $name = $item['name'];
            if (
                str_starts_with($name, $code.' –')
                || str_starts_with($name, $code.' -')
                || str_contains($name, 'Solicitud #'.$process->id)
            ) {
                return $item['id'];
            }
        }

        return null;
    }

    protected function resolveRegistrationFolderId(Registration $registration, string $baseId): ?string
    {
        if ($registration->drive_folder_id) {
            return $registration->drive_folder_id;
        }

        $folderName = $registration->product_name.' - '.($registration->registration_number ?? 'Sin Número');

        if ($registration->company_id && $registration->company) {
            $companyFolderId = $this->resolveCompanyFolderId($registration->company, $baseId);
            if ($companyFolderId) {
                return $this->drive->findFolderByNameUnder($companyFolderId, $folderName);
            }
        }

        $noClientName = $this->settings->drive_folder_name_no_client ?: 'Solicitudes sin cliente';
        $noClientId = $this->drive->findFolderByNameUnder($baseId, $noClientName);
        if ($noClientId) {
            return $this->drive->findFolderByNameUnder($noClientId, $folderName);
        }

        return null;
    }

    protected function companyFolderName(Company $company): string
    {
        return $company->name.' - '.($company->nit_rut ?? 'Sin NIT');
    }

    /**
     * @param  array<string, true>  $protected
     */
    protected function markFolderTreeProtected(string $rootFolderId, array &$protected): void
    {
        $protected[$rootFolderId] = true;
        foreach ($this->drive->listDescendantFolders($rootFolderId) as $folderId => $folder) {
            $protected[$folderId] = true;
        }
    }

    /**
     * @param  array<string, true>  $protected
     */
    protected function protectFolderAndAncestors(string $folderId, string $baseId, array &$protected): void
    {
        if ($folderId === '') {
            return;
        }

        $protected[$folderId] = true;
        foreach ($this->drive->getFolderAncestorIds($folderId, $baseId) as $ancestorId) {
            $protected[$ancestorId] = true;
        }
    }

    /**
     * Protege las carpetas contenedoras de un archivo (documento suelto en Drive).
     *
     * @param  array<string, true>  $protected
     */
    protected function protectItemAncestors(string $driveId, string $baseId, array &$protected): void
    {
        if ($driveId === '') {
            return;
        }

        foreach ($this->drive->getFolderAncestorIds($driveId, $baseId) as $ancestorId) {
            $protected[$ancestorId] = true;
        }
    }

    /**
     * @param  array<string, array{id: string, name: string, parent_id: string}>  $folders
     * @return array<string, string>
     */
    protected function buildFolderPathMap(string $baseId, array $folders): array
    {
        $paths = [];
        foreach ($folders as $folderId => $folder) {
            $segments = [$folder['name']];
            $parentId = $folder['parent_id'];
            $guard = 0;
            while ($parentId && $parentId !== $baseId && $guard < 30) {
                if (isset($folders[$parentId])) {
                    array_unshift($segments, $folders[$parentId]['name']);
                    $parentId = $folders[$parentId]['parent_id'];
                } else {
                    break;
                }
                $guard++;
            }
            $paths[$folderId] = implode(' / ', $segments);
        }

        return $paths;
    }

    /**
     * @param  array<string, array{id: string, name: string, parent_id: string}>  $folders
     */
    protected function folderDepth(string $folderId, array $folders): int
    {
        $depth = 0;
        $currentId = $folderId;
        $guard = 0;
        while (isset($folders[$currentId]) && $guard < 50) {
            $depth++;
            $currentId = $folders[$currentId]['parent_id'];
            $guard++;
        }

        return $depth;
    }
}
