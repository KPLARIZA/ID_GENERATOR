<?php

namespace App\Filament\Resources\EmployeeIds\Pages;

use App\Filament\Resources\EmployeeIds\EmployeeIdResource;
use App\Services\IDCardGenerator;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditEmployeeId extends EditRecord
{
    protected static string $resource = EmployeeIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateIDCard')
                ->label('Generate ID Card')
                ->icon('heroicon-o-document')
                ->action(function () {
                    try {
                        $generator = new IDCardGenerator();
                        $filename = $generator->generate($this->record);
                        
                        // Update the record with the generated ID card path
                        $this->record->update(['id_card_image' => $filename]);
                        
                        // Download the generated ID card
                        return response()->download(Storage::disk('public')->path($filename), 
                            'ID_' . $this->record->id_number . '.png');
                    } catch (\Exception $e) {
                        $this->addError('id_card_generation', 'Failed to generate ID card: ' . $e->getMessage());
                    }
                })
                ->requiresConfirmation()
                ->color('success'),
            
            DeleteAction::make(),
        ];
    }
}

