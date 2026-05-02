<?php

namespace App\Filament\Resources\EmployeeIds\Pages;

use App\Filament\Resources\EmployeeIds\EmployeeIdResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeId extends ViewRecord
{
    protected static string $resource = EmployeeIdResource::class;

    protected string $view = 'filament.resources.employee-ids.pages.view-employee-id';
}
