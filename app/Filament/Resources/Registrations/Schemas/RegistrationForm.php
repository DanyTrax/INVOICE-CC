<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Company;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Cliente')
                    ->relationship('company', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Select::make('assigned_specialist_id')
                    ->label('Especialista Asignado')
                    ->relationship('assignedSpecialist', 'name')
                    ->searchable()
                    ->preload(),
                
                TextInput::make('product_name')
                    ->label('Nombre del Producto')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('registration_number')
                    ->label('Número de Registro')
                    ->maxLength(255),
                
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'vigente' => 'Vigente',
                        'tramite' => 'En Trámite',
                        'requerimiento' => 'Requerimiento',
                        'vencido' => 'Vencido',
                    ])
                    ->default('tramite')
                    ->required(),
                
                TextInput::make('transaction_type')
                    ->label('Tipo de Trámite')
                    ->maxLength(255),
                
                TextInput::make('quotation_number')
                    ->label('No. Cotización / Factura')
                    ->maxLength(255),
                
                DatePicker::make('client_request_date')
                    ->label('Fecha Solicitud Cliente'),
                
                DatePicker::make('radication_date')
                    ->label('Fecha Radicación'),
                
                TextInput::make('radication_number')
                    ->label('Radicado No.')
                    ->maxLength(255),
                
                TextInput::make('key_code')
                    ->label('Llave / Código')
                    ->maxLength(255),
                
                DatePicker::make('expiration_date')
                    ->label('Fecha de Vencimiento'),
                
                Textarea::make('observations')
                    ->label('Observaciones')
                    ->rows(3),
            ]);
    }
}
