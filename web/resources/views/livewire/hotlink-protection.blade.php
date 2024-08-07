<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">{{ $mainTitle }}</h1>
    </div>

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

        <button type="submit" class="bg-blue-500 dark:before:bg-primary-500 font-bold border py-2 px-4 mt-6 rounded">
            Submit
        </button>
    </form>

    <x-filament-actions::modals />

</div>
