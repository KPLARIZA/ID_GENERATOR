<?php

namespace App\Filament\Resources\EmployeeIds\Schemas;

use App\Models\EmployeeId;
use Filament\Forms\Components\FileUpload;
<<<<<<< HEAD
=======
use Filament\Forms\Components\Select;
>>>>>>> Shunbranch
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeIdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employee Information')
                    ->components([
                        TextInput::make('id_number')
                            ->label('ID Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., 2024001'),
                        
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->placeholder('John'),
                        
                        TextInput::make('middle_initial')
                            ->label('Middle Initial')
                            ->maxLength(1)
                            ->placeholder('D'),
                        
                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->placeholder('Doe'),
                    ])
                    ->columns(2),
                
                Section::make('Position & Office')
                    ->components([
                        TextInput::make('designation')
                            ->label('Designation')
                            ->required()
                            ->placeholder('Provincial Governor'),
                        
                        TextInput::make('office_name')
                            ->label('Office Name')
                            ->required()
                            ->placeholder('Office of the Provincial Governor'),
                        
                        TextInput::make('extension')
                            ->label('Extension/Phone')
                            ->placeholder('101'),
                    ])
                    ->columns(2),
                
                Section::make('Profile Picture')
                    ->components([
                        FileUpload::make('profile_picture')
                            ->label('Profile Picture')
                            ->image()
                            ->directory('profile-pictures')
                            ->maxSize(5120)
                            ->helperText('Optional - Upload a profile picture for the ID card (max 5MB)'),
                    ]),

                Section::make('Print tracking')
                    ->components([
                        Select::make('print_status')
                            ->label('Print Status')
                            ->options(EmployeeId::getPrintStatusOptions())
                            ->default(EmployeeId::PRINT_STATUS_IN_PROGRESS)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}



