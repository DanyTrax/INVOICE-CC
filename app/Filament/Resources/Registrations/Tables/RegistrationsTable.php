<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Models\Registration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Registro / Expediente')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size(TextColumnSize::Large),
                
                TextColumn::make('registration_number')
                    ->label('Número')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size(TextColumnSize::Small),
                
                TextColumn::make('assignedSpecialist.name')
                    ->label('Especialista')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vigente' => 'success',
                        'tramite' => 'warning',
                        'requerimiento' => 'info',
                        'vencido' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vigente' => 'Vigente',
                        'tramite' => 'En Trámite',
                        'requerimiento' => 'Requerimiento',
                        'vencido' => 'Vencido',
                        default => $state,
                    }),
                
                TextColumn::make('expiration_date')
                    ->label('Vencimiento')
                    ->date('d M, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'vigente' => 'Vigente',
                        'tramite' => 'En Trámite',
                        'requerimiento' => 'Requerimiento',
                        'vencido' => 'Vencido',
                    ]),
                
                SelectFilter::make('company_id')
                    ->label('Cliente')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable();
    }
}
