<x-filament-panels::page>
    <div>

        <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4 mb-5">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections[0]['helperTexts'] }}
            </p>
        </div>

        <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4 mb-5">
            <p class="text-base text-gray-400 mb-5">
                Hotlink is currently {{ $this->state['enabled'] }}.
            </p>
            {{ $this->updateEnabled }}
        </div>

        <div class="mb-10 mt-6 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections[1]['title'] }}</h1>
        </div>
        <form wire:submit.prevent="update">
            {{ $this->form }}

            <div class="mb-10 mt-6 mx-4">
                <x-filament::button type="submit">
                    Submit
                </x-filament::button>
            </div>

        </form>

        <x-filament-actions::modals/>

    </div>
</x-filament-panels::page>
