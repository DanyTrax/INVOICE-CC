<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                EditAction::make(),
            ])
            ->defaultSort('name')
            ->searchable();
    }
}
