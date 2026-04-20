<?php

namespace App\Filament\Resources\EmployeeIds\Pages;

use App\Filament\Resources\EmployeeIds\EmployeeIdResource;
use App\Services\IDCardGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeId extends CreateRecord
{
    protected static string $resource = EmployeeIdResource::class;

    protected function afterCreate(): void
    {
        // Automatically generate ID card after creating the record
        try {
            $generator = new IDCardGenerator();
            $filename = $generator->generate($this->record);
            $this->record->update(['id_card_image' => $filename]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to auto-generate ID card: ' . $e->getMessage());
        }
    }
}

