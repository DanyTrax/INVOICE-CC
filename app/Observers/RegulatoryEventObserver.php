<?php

namespace App\Observers;

use App\Models\Process;
use App\Models\RegulatoryEvent;
use Carbon\Carbon;

class RegulatoryEventObserver
{
    /**
     * Al crear un evento tipo AUTO: calcular due_date = notification_date + 90 días hábiles.
     */
    public function creating(RegulatoryEvent $event): void
    {
        if ($event->event_type !== RegulatoryEvent::EVENT_TYPE_AUTO) {
            return;
        }
        if ($event->due_date !== null) {
            return;
        }
        $notificationDate = $event->notification_date;
        if (!$notificationDate) {
            return;
        }
        $date = $notificationDate instanceof \DateTimeInterface
            ? Carbon::parse($notificationDate)
            : Carbon::parse($notificationDate);
        $event->due_date = $date->copy()->addWeekdays(90);
    }

    /**
     * Tras crear el evento: AUTO → proceso pasa a En Requerimiento; RESOLUCION → proceso Finalizado.
     */
    public function created(RegulatoryEvent $event): void
    {
        $process = $event->submission?->process;
        if (!$process instanceof Process) {
            return;
        }
        if ($event->event_type === RegulatoryEvent::EVENT_TYPE_AUTO) {
            $process->update(['status' => Process::STATUS_EN_REQUERIMIENTO]);
        }
        if ($event->event_type === RegulatoryEvent::EVENT_TYPE_RESOLUCION) {
            $process->update(['status' => Process::STATUS_FINALIZADO]);
        }
    }
}
