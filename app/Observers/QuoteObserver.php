<?php

namespace App\Observers;

use App\Models\Quote;
use App\Models\Process;

class QuoteObserver
{
    public function updated(Quote $quote): void
    {
        // Lógica anterior de creación automática de procesos al aprobar la cotización
        // ha sido desactivada. La creación y vinculación de expedientes se hace
        // manualmente desde el módulo Expedientes / Procesos.
        return;
    }
}
