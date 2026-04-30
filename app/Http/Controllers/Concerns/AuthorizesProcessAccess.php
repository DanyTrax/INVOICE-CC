<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Process;
use App\Services\ProcessAccessService;

trait AuthorizesProcessAccess
{
    protected function authorizeProcessView(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canViewProcess(auth()->user(), $process),
            403,
            'No tienes acceso a esta solicitud.'
        );
    }

    protected function authorizeProcessFeed(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canFeedTimelineOnProcess(auth()->user(), $process),
            403,
            'No tienes permiso para modificar la línea de tiempo de esta solicitud.'
        );
    }

    protected function authorizeProcessDocuments(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canManageDocumentsOnProcess(auth()->user(), $process),
            403,
            'No tienes permiso para gestionar documentos de esta solicitud.'
        );
    }

    /**
     * Subir archivos a la carpeta Drive de la solicitud (alimentar línea de tiempo o gestión documental).
     */
    protected function authorizeProcessDocumentUpload(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canUploadDocumentsOnProcess(auth()->user(), $process),
            403,
            'No tienes permiso para subir documentos en esta solicitud.'
        );
    }

    protected function authorizeProcessDelete(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canDeleteProcess(auth()->user(), $process),
            403,
            'No tienes permiso para eliminar esta solicitud.'
        );
    }
}
