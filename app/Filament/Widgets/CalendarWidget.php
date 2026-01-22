<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class CalendarWidget extends Widget
{
    protected string $view = 'filament.widgets.calendar-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public function getEvents(): array
    {
        $expiring = Registration::whereNotNull('expiration_date')
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => 'exp_' . $reg->id,
                    'title' => 'Vence: ' . Str::limit($reg->product_name, 20),
                    'start' => $reg->expiration_date->format('Y-m-d'),
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#dc2626',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'vencimiento',
                        'registration_id' => $reg->id,
                    ],
                ];
            });
        
        $responseDeadlines = Registration::whereNotNull('response_limit_date')
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => 'resp_' . $reg->id,
                    'title' => 'Radicar: ' . Str::limit($reg->product_name, 20),
                    'start' => $reg->response_limit_date->format('Y-m-d'),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#2563eb',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'radicacion',
                        'registration_id' => $reg->id,
                    ],
                ];
            });
        
        return $expiring->merge($responseDeadlines)->values()->toArray();
    }
}
