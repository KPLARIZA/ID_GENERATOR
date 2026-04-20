<?php

namespace App\Filament\Resources\EmployeeIds\Pages;

use App\Filament\Resources\EmployeeIds\EmployeeIdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeIds extends ListRecords
{
    protected static string $resource = EmployeeIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create New ID'),
        ];
    }
}

