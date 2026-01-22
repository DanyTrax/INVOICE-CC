<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Company;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Secci?n 1: Informaci?n del Cliente (Autom?tico)
                Section::make('1. Informaci?n del Cliente (Autom?tico)')
                    ->schema([
                        Select::make('company_id')
                            ->label('Cliente Seleccionado')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $company = Company::find($state);
                                    if ($company) {
                                        $set('company_nit', $company->nit_rut);
                                        $set('company_address', $company->address);
                                        $set('company_contact', $company->contact_person_name);
                                    }
                                } else {
                                    $set('company_nit', null);
                                    $set('company_address', null);
                                    $set('company_contact', null);
                                }
                            }),
                        
                        TextInput::make('company_nit')
                            ->label('NIT')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn ($get) => Company::find($get('company_id'))?->nit_rut),
                        
                        TextInput::make('company_address')
                            ->label('Direcci?n')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn ($get) => Company::find($get('company_id'))?->address),
                        
                        TextInput::make('company_contact')
                            ->label('Persona Contacto')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn ($get) => Company::find($get('company_id'))?->contact_person_name),
                    ])
                    ->columns(4),
                
                // Sección 2: Datos del Trámite y Producto
                Section::make('2. Datos del Trámite y Producto')
                    ->schema([
                        TextInput::make('product_name')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(4),
                        
                        Select::make('assigned_specialist_id')
                            ->label('Especialista Asignado (Agente)')
                            ->relationship('assignedSpecialist', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpan(4),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'vigente' => 'Vigente',
                                'tramite' => 'En Trámite',
                                'requerimiento' => 'Requerimiento',
                                'vencido' => 'Vencido',
                            ])
                            ->default('tramite')
                            ->required()
                            ->columnSpan(4),
                        
                        Select::make('transaction_type')
                            ->label('Tipo de Tr?mite')
                            ->options([
                                'Nuevo' => 'Nuevo',
                                'Renovación' => 'Renovación',
                                'Modificación' => 'Modificación',
                                'Cancelación' => 'Cancelación',
                            ])
                            ->columnSpan(4),
                        
                        TextInput::make('quotation_number')
                            ->label('No. Cotizaci?n / Factura')
                            ->maxLength(255)
                            ->columnSpan(4),
                    ])
                    ->columns(12),
                
                // Secci?n 3: Cronograma y Radicados
                Section::make('3. Cronograma y Radicados')
                    ->schema([
                        DatePicker::make('client_request_date')
                            ->label('Fecha Solicitud Cliente')
                            ->columnSpan(3),
                        
                        DatePicker::make('radication_date')
                            ->label('Fecha Radicaci?n')
                            ->columnSpan(3),
                        
                        TextInput::make('radication_number')
                            ->label('Radicado No.')
                            ->maxLength(255)
                            ->columnSpan(3),
                        
                        TextInput::make('key_code')
                            ->label('Llave / C?digo')
                            ->maxLength(255)
                            ->columnSpan(3),
                        
                        DatePicker::make('submission_date')
                            ->label('Fecha de Presentaci?n')
                            ->columnSpan(3),
                        
                        DatePicker::make('expiration_date')
                            ->label('Fecha de Vencimiento')
                            ->columnSpan(3),
                        
                        DatePicker::make('invima_auto_date')
                            ->label('Fecha Auto INVIMA')
                            ->columnSpan(3),
                        
                        DatePicker::make('response_limit_date')
                            ->label('Fecha L?mite de Respuesta')
                            ->columnSpan(3),
                        
                        DatePicker::make('response_radication_date')
                            ->label('Fecha Radicaci?n de Respuesta')
                            ->columnSpan(3),
                        
                        TextInput::make('resolution_number')
                            ->label('N?mero de Resoluci?n')
                            ->maxLength(255)
                            ->columnSpan(3),
                    ])
                    ->columns(12),
                
                // Secci?n 4: Detalles y Observaciones
                Section::make('4. Detalles y Observaciones')
                    ->schema([
                        Textarea::make('client_requirement')
                            ->label('Requerimiento del Cliente')
                            ->rows(3)
                            ->columnSpan(6),
                        
                        Textarea::make('invima_requirement')
                            ->label('Requerimiento INVIMA')
                            ->rows(3)
                            ->columnSpan(6),
                        
                        Textarea::make('pending_docs')
                            ->label('Documentos Pendientes')
                            ->rows(3)
                            ->columnSpan(6),
                        
                        Textarea::make('observations')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpan(6),
                    ])
                    ->columns(12),
                
                // Sección 5: Documentos (Placeholder para Google Drive)
                Section::make('4. Documentos en Drive')
                    ->description('Los documentos se subirán directamente a la carpeta del cliente en Google Drive')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('drive_info')
                            ->label('')
                            ->content('La integración con Google Drive se configurará en la página de Configuración. Los archivos se organizarán automáticamente en: /RAMS/{Cliente}/{Expediente}/')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
