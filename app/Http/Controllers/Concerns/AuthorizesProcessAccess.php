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
            'No tienes acceso a este expediente.'
        );
    }

    protected function authorizeProcessFeed(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canFeedTimelineOnProcess(auth()->user(), $process),
            403,
            'No tienes permiso para modificar la línea de tiempo de este expediente.'
        );
    }

    protected function authorizeProcessDocuments(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canManageDocumentsOnProcess(auth()->user(), $process),
            403,
            'No tienes permiso para gestionar documentos de este expediente.'
        );
    }

    protected function authorizeProcessDelete(Process $process): void
    {
        abort_unless(
            app(ProcessAccessService::class)->canDeleteProcess(auth()->user(), $process),
            403,
            'No tienes permiso para eliminar este expediente.'
        );
    }
}
