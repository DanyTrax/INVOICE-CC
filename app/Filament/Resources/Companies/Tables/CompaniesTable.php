<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('nit_rut')
                    ->label('NIT')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size(TextColumnSize::Small),
                
                TextColumn::make('contact_person_name')
                    ->label('Contacto')
                    ->searchable(),
                
                TextColumn::make('contact_person_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('registrations_count')
                    ->label('Registros')
                    ->counts('registrations')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('viewRegistrations')
                    ->label('Ver Expedientes')
                    ->icon('heroicon-o-folder-open')
                    ->color('info')
                    ->url(fn ($record) => \App\Filament\Resources\Registrations\RegistrationResource::getUrl('index', ['tableFilters' => ['company_id' => ['value' => $record->id]]])),
                
                Action::make('invite')
                    ->label('Invitar')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('template_id')
                            ->label('Plantilla de Email')
                            ->options(\App\Models\EmailTemplate::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('Email del Cliente')
                            ->email()
                            ->default(fn ($record) => $record->contact_person_email)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        // TODO: Implementar envío de email con plantilla
                        \Filament\Notifications\Notification::make()
                            ->title('Invitación enviada a ' . $data['email'])
                            ->success()
                            ->send();
                    }),
                
                EditAction::make(),
            ])
            ->defaultSort('name')
            ->searchable();
    }
}
