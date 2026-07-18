@php
    use Filament\Support\Enums\ActionSize;
@endphp

<x-filament-panels::page>
    <form wire:submit.prevent="import" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit" size="lg">
                {{ __('Import Borrowers') }}
            </x-filament::button>

            <x-filament::button
                wire:click="downloadSampleCsv"
                color="gray"
                outlined
                size="lg"
            >
                {{ __('Download Template') }}
            </x-filament::button>
        </div>
    </form>

    @if ($showReport)
        <div class="mt-8 space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Import Report') }}</h3>
                
                <!-- Summary Stats -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">{{ __('Total Rows') }}</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $importReport['total_rows'] }}</p>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">{{ __('Successful') }}</p>
                        <p class="text-2xl font-bold text-green-600">{{ $importReport['successful'] }}</p>
                    </div>
                    
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">{{ __('Failed') }}</p>
                        <p class="text-2xl font-bold text-red-600">{{ $importReport['failed'] }}</p>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">{{ __('Success Rate') }}</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $importReport['success_rate'] }}%</p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            class="bg-green-600 h-2 rounded-full" 
                            style="width: {{ $importReport['success_rate'] }}%"
                        ></div>
                    </div>
                </div>

                <!-- Errors -->
                @if (!empty($importReport['errors']))
                    <div class="mb-4">
                        <h4 class="font-semibold text-red-600 mb-2">{{ __('Errors') }} ({{ count($importReport['errors']) }})</h4>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 max-h-48 overflow-y-auto">
                            <ul class="space-y-1">
                                @foreach ($importReport['errors'] as $error)
                                    <li class="text-sm text-red-700">• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Warnings -->
                @if (!empty($importReport['warnings']))
                    <div class="mb-4">
                        <h4 class="font-semibold text-amber-600 mb-2">{{ __('Warnings') }} ({{ count($importReport['warnings']) }})</h4>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 max-h-48 overflow-y-auto">
                            <ul class="space-y-1">
                                @foreach ($importReport['warnings'] as $warning)
                                    <li class="text-sm text-amber-700">• {{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="font-semibold text-blue-900 mb-2">{{ __('Import Guidelines') }}</h4>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>✓ {{ __('CSV file must have headers in the first row') }}</li>
            <li>✓ {{ __('Required columns: name, mobile_number') }}</li>
            <li>✓ {{ __('Email is optional') }}</li>
            <li>✓ {{ __('Mobile numbers must be unique within the cooperative') }}</li>
            <li>✓ {{ __('Maximum file size: 10MB') }}</li>
            <li>✓ {{ __('Duplicate borrowers will be skipped') }}</li>
        </ul>
    </div>
</x-filament-panels::page>
