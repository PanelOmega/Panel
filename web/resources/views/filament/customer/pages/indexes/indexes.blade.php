{{--<div>--}}
{{--    @livewire('indexes.indexes', [--}}
{{--        'mainTitle' => $mainTitle,--}}
{{--        'sections' => $sections--}}
{{--    ])--}}
{{--</div>--}}
<x-filament-panels::page>
    <div>

        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">{{ $mainTitle }}</h1>
        </div>

        <div x-data="{ open: false }" class="mb-6">
            <p class="mt-4">
                {{ $sections['section_title'] }}
                <button @click="open = !open" class="underline">
                    <span x-text="open ? 'Hide example index files' : 'Show example index files'"></span>
                </button>
            </p>

            <div x-show="open" class="mt-4">
                <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4">
                    <h2 class="text-xl font-semibold dark:text-white">{{ $sections['subtitle'] }}</h2>
                    <p>{{ $sections['section_subtitle'] }}</p>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="update">
            {{ $this->form }}

            <button type="submit" class="bg-blue-500 dark:before:bg-primary-500 font-bold border py-2 px-4 mt-6 rounded">
                Add Index
            </button>
        </form>

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
