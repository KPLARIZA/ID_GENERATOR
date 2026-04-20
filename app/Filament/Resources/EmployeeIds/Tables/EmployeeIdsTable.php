<?php

namespace App\Filament\Resources\EmployeeIds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class EmployeeIdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_number')
                    ->label('ID Number')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('designation')
                    ->label('Designation')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('office_name')
                    ->label('Office')
                    ->sortable()
                    ->searchable(),
                
                ImageColumn::make('profile_picture')
                    ->label('Picture')
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

