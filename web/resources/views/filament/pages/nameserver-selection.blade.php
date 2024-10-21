<x-filament-panels::page>

    <div>
        <div class="mt-10 mb-5 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle'] }}</h1>
        </div>
        <div class="mt-6">
            <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4">
                <p>{{ $sections['subtitle_text'][0] }}</p>
            </div>
            <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4">
                <p>{{ $sections['subtitle_text'][1] }}</p>
            </div>
        </div>

        <div class="mt-6">
            {{ $this->form }}
        </div>
    </div>
    <div class="mt-6">
        <x-filament::button
            wire:click="update"
            color="primary">
            Save
        </x-filament::button>
    </div>

</x-filament-panels::page>
