<?php

namespace App\Observers;

use App\Models\Quote;
use App\Models\Process;

class QuoteObserver
{
    /**
     * Cuando la cotización pasa a estado Aprobada, crear un Process por cada quote_item
     * (solo si ese ítem aún no tiene proceso).
     */
    public function updated(Quote $quote): void
    {
        if ($quote->status !== 'Aprobada') {
            return;
        }

        $quote->loadMissing('quoteItems');

        foreach ($quote->quoteItems as $item) {
            if ($item->process()->exists()) {
                continue;
            }
            Process::create([
                'quote_item_id' => $item->id,
                'client_id' => $quote->client_id,
                'status' => Process::STATUS_RECOLECCION,
                'expediente_invima' => null,
            ]);
        }
    }
}
