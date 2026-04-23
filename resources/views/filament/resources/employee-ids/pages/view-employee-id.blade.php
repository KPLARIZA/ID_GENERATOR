<x-filament-panels::page>
    <div class="mx-auto max-w-4xl">
        <div class="grid grid-cols-1 gap-6">
            <!-- Employee Information -->
            <x-filament::section>
                <x-slot name="heading">
                    Employee Information
                </x-slot>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">ID Number</p>
                        <div class="text-lg font-bold text-primary-600">
                            {{ $this->record->id_number }}
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Full Name</p>
                        <div>{{ $this->record->full_name }}</div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Designation</p>
                        <div>{{ $this->record->designation }}</div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Office</p>
                        <div>{{ $this->record->office_name }}</div>
                    </div>
                </div>
            </x-filament::section>

            <!-- Profile Picture -->
            @if($this->record->profile_picture)
            <x-filament::section>
                <x-slot name="heading">
                    Profile Picture
                </x-slot>

                <div class="flex justify-center">
                    <img src="{{ Storage::disk('public')->url($this->record->profile_picture) }}" 
                         alt="Profile Picture" 
                         class="h-64 w-64 rounded-lg object-cover shadow-lg">
                </div>
            </x-filament::section>
            @endif

            <!-- Generated ID Card -->
            @if($this->record->id_card_image)
            <x-filament::section>
                <x-slot name="heading">
                    Generated ID Document
                </x-slot>

                <div class="flex flex-col items-center gap-4">
                    <div class="flex gap-2">
                        <a href="{{ Storage::disk('public')->url($this->record->id_card_image) }}" 
                           download="ID_{{ $this->record->id_number }}.docx"
                           class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Download ID DOCX
                        </a>
                    </div>
                </div>
            </x-filament::section>
            @else
            <x-filament::section>
                <x-slot name="heading">
                    ID Document
                </x-slot>
                <p class="text-sm text-gray-600">
                    No ID document has been generated yet. Click the "Generate ID Card" button to create one.
                </p>
            </x-filament::section>
            @endif
        </div>
    </div>
</x-filament-panels::page>
