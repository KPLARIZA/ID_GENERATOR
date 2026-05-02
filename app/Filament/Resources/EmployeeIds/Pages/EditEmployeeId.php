<?php

namespace App\Filament\Resources\EmployeeIds\Pages;

use App\Filament\Resources\EmployeeIds\EmployeeIdResource;
use App\Services\IDCardGenerator;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
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
                ->action(function (): void {
                    try {
                        $generator = new IDCardGenerator();
                        $filename = $generator->generate($this->record);
                        $this->record->update(['id_card_image' => $filename]);

                        Notification::make()
                            ->title('ID document generated successfully')
                            ->actions([
                                NotificationAction::make('download')
                                    ->label('Download DOCX')
                                    ->url(Storage::disk('public')->url($filename), shouldOpenInNewTab: true),
                            ])
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to generate ID card')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->color('success'),
            
            DeleteAction::make(),
        ];
    }
}

