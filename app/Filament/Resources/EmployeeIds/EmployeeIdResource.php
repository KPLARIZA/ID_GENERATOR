<?php

namespace App\Filament\Resources\EmployeeIds;

use App\Filament\Resources\EmployeeIds\Pages\CreateEmployeeId;
use App\Filament\Resources\EmployeeIds\Pages\EditEmployeeId;
use App\Filament\Resources\EmployeeIds\Pages\ListEmployeeIds;
use App\Filament\Resources\EmployeeIds\Pages\ViewEmployeeId;
use App\Filament\Resources\EmployeeIds\Schemas\EmployeeIdForm;
use App\Models\EmployeeId;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeIdResource extends Resource
{
    protected static ?string $model = EmployeeId::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schemas\Schema $schema): Schemas\Schema
    {
        return EmployeeIdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_number')
                    ->label('ID Number')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('designation')
                    ->label('Designation')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('office_name')
                    ->label('Office')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label('Picture')
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeIds::route('/'),
            'create' => CreateEmployeeId::route('/create'),
            'view' => ViewEmployeeId::route('/{record}'),
            'edit' => EditEmployeeId::route('/{record}/edit'),
        ];
    }
}
