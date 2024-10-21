<x-filament-panels::page>

    <div>
        <p class="text-base text-gray-400 mb-5">
{{--            {{ $sections['title_text'][0] }}--}}
        </p>
        <div class="mt-6">
{{--            <h2 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle'][0] }}</h2>--}}
        </div>
        <div class="mt-6">
            {{ $this->form }}
        </div>
    </div>
    <div class="mt-6">
        <x-filament::button
            wire:click="update"
            color="primary">
            Save Changes
        </x-filament::button>
    </div>

</x-filament-panels::page>
