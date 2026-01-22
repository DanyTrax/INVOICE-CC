<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\Widget;

class CalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.calendar-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public function getEvents(): array
    {
        $expiring = Registration::whereNotNull('expiration_date')
            ->where('expiration_date', '>=', now()->startOfMonth())
            ->where('expiration_date', '<=', now()->endOfMonth())
            ->get()
            ->map(function ($reg) {
                return [
                    'title' => 'Vence: ' . $reg->product_name,
                    'date' => $reg->expiration_date->format('Y-m-d'),
                    'color' => 'red',
                ];
            });
        
        $responseDeadlines = Registration::whereNotNull('response_limit_date')
            ->where('response_limit_date', '>=', now()->startOfMonth())
            ->where('response_limit_date', '<=', now()->endOfMonth())
            ->get()
            ->map(function ($reg) {
                return [
                    'title' => 'Radicar: ' . $reg->product_name,
                    'date' => $reg->response_limit_date->format('Y-m-d'),
                    'color' => 'blue',
                ];
            });
        
        return $expiring->merge($responseDeadlines)->toArray();
    }
}
