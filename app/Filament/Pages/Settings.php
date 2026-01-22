<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Settings extends Page
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    
    protected static ?string $navigationLabel = 'Configuración';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Sistema';
    
    protected static ?int $navigationSort = 2;
    
    protected string $view = 'filament.pages.settings';
    
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
