<x-filament-panels::page>
    <div class="space-y-6">
        @if($this->record)
            <x-filament::section>
                <x-slot name="heading">
                    Batch Overview
                </x-slot>
                <x-slot name="description">
                    Key information about {{ $this->record->name }}
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Batch Number</p>
                        <p class="text-lg font-semibold">{{ $this->record->batch_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                        <p class="text-lg font-semibold">{{ $this->record->status->label() }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</p>
                        <p class="text-lg font-semibold">{{ $this->record->start_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
