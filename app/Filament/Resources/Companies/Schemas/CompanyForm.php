<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la Empresa')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('nit_rut')
                    ->label('NIT / RUT')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                TextInput::make('address')
                    ->label('Dirección')
                    ->maxLength(500),
                
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),
                
                TextInput::make('contact_person_name')
                    ->label('Persona de Contacto')
                    ->maxLength(255),
                
                TextInput::make('contact_person_email')
                    ->label('Email de Contacto')
                    ->email()
                    ->maxLength(255),
            ]);
    }
}
