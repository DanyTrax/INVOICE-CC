<?php

namespace App\Services;

use App\Models\CapacitacionVideo;
use App\Models\Company;
use App\Models\Process;
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

        $protected = $this->buildProtectedFolderMap($baseId);
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
            'orphan_folders' => $orphans,
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }

    /**
     * @return array<string, true>
     */
    protected function buildProtectedFolderMap(string $baseId): array
    {
        $protected = [$baseId => true];

        foreach (self::SYSTEM_FOLDER_NAMES as $systemName) {
            $systemId = $this->drive->findFolderByNameUnder($baseId, $systemName);
            if ($systemId) {
                $this->markFolderTreeProtected($systemId, $protected);
            }
        }

        $capRoot = $this->drive->findFolderByNameUnder($baseId, 'Capacitaciones');
        if ($capRoot) {
            $protected[$capRoot] = true;
        }

        foreach (CapacitacionVideo::query()->whereNotNull('drive_folder_id')->pluck('drive_folder_id') as $folderId) {
            $this->protectFolderAndAncestors((string) $folderId, $baseId, $protected);
        }

        foreach (Company::query()->whereNotNull('drive_folder_id')->pluck('drive_folder_id') as $folderId) {
            $this->protectFolderAndAncestors((string) $folderId, $baseId, $protected);
        }

        foreach (Process::query()->whereNotNull('drive_folder_id')->pluck('drive_folder_id') as $folderId) {
            $this->protectFolderAndAncestors((string) $folderId, $baseId, $protected);
        }

        foreach (Registration::query()->whereNotNull('drive_folder_id')->pluck('drive_folder_id') as $folderId) {
            $this->protectFolderAndAncestors((string) $folderId, $baseId, $protected);
        }

        $countries = Company::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country')
            ->map(fn ($c) => trim((string) $c))
            ->filter();

        foreach ($countries as $country) {
            $countryId = $this->drive->findFolderByNameUnder($baseId, $country);
            if ($countryId) {
                $protected[$countryId] = true;
            }
        }

        $noClientName = $this->settings->drive_folder_name_no_client ?: 'Solicitudes sin cliente';
        $noClientId = $this->drive->findFolderByNameUnder($baseId, $noClientName);
        if ($noClientId) {
            $protected[$noClientId] = true;
        }

        if (Company::query()->where(fn ($q) => $q->whereNull('country')->orWhere('country', ''))->exists()) {
            $clientsName = $this->settings->drive_folder_name_with_client ?: 'Clientes';
            $clientsId = $this->drive->findFolderByNameUnder($baseId, $clientsName);
            if ($clientsId) {
                $protected[$clientsId] = true;
            }
        }

        return $protected;
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
