<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class CalendarWidget extends Widget
{
    protected string $view = 'filament.widgets.calendar-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public ?string $currentMonth = null;
    
    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
    }
    
    public function getCurrentMonth(): Carbon
    {
        return $this->currentMonth 
            ? Carbon::parse($this->currentMonth . '-01')
            : now()->startOfMonth();
    }
    
    public function previousMonth(): void
    {
        $this->currentMonth = $this->getCurrentMonth()->subMonth()->format('Y-m');
    }
    
    public function nextMonth(): void
    {
        $this->currentMonth = $this->getCurrentMonth()->addMonth()->format('Y-m');
    }
    
    public function getEvents(): array
    {
        $month = $this->getCurrentMonth();
        
        $expiring = Registration::whereNotNull('expiration_date')
            ->where('expiration_date', '>=', $month->copy()->startOfMonth())
            ->where('expiration_date', '<=', $month->copy()->endOfMonth())
            ->get()
            ->map(function ($reg) {
                return [
                    'title' => 'Vence: ' . Str::limit($reg->product_name, 15),
                    'date' => $reg->expiration_date->format('Y-m-d'),
                    'color' => 'red',
                ];
            });
        
        $responseDeadlines = Registration::whereNotNull('response_limit_date')
            ->where('response_limit_date', '>=', $month->copy()->startOfMonth())
            ->where('response_limit_date', '<=', $month->copy()->endOfMonth())
            ->get()
            ->map(function ($reg) {
                return [
                    'title' => 'Radicar: ' . Str::limit($reg->product_name, 15),
                    'date' => $reg->response_limit_date->format('Y-m-d'),
                    'color' => 'blue',
                ];
            });
        
        return $expiring->merge($responseDeadlines)->toArray();
    }
}
