<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Expedientes Activos', Registration::where('status', '!=', 'vencido')->count())
                ->description('Total de registros activos')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            
            Stat::make('Vencen este mes', Registration::where('status', 'vencido')
                ->orWhere(function($query) {
                    $query->where('expiration_date', '>=', now()->startOfMonth())
                          ->where('expiration_date', '<=', now()->endOfMonth());
                })->count())
                ->description('Requieren atención')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            
            Stat::make('En Trámite INVIMA', Registration::where('status', 'tramite')->count())
                ->description('Pendientes de respuesta')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
            
            Stat::make('Clientes Totales', Company::count())
                ->description('Empresas registradas')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('gray'),
        ];
    }
}
