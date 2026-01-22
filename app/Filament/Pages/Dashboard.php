<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Inicio';
    
    protected static ?int $navigationSort = 0;
    
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\CalendarWidget::class,
        ];
    }
}
