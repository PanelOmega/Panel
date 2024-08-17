<x-filament-panels::page>

    <div>

        <div class="mb-6">
            <h2 class="text-xl text-gray-400 mb-5">{{ $sections[0]['title'] }}</h2>
        </div>

        <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4 mb-5">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections[0]['helperTexts'][0] }}
            </p>
            <p class="text-base text-gray-400">
                {!! $sections[0]['helperTexts'][1] !!}
            </p>
        </div>

        <form wire:submit.prevent="update">
            {{ $this->form }}

            <button type="submit"
                    class="dark:before:bg-primary-500 font-bold border py-2 px-4 mt-6 rounded">
                Change Password
            </button>
        </form>

        <x-filament-actions::modals/>

        <div class="mb-10 mt-6 mx-4">
            <h2 class="text-xl text-gray-400 mb-5">{{ $sections[1]['title'] }}</h2>
        </div>

        <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4 mb-5">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections[1]['helperTexts'][0] }}
            </p>
        </div>

        <div class="mb-6">
            <h2 class="text-xl text-gray-400 mb-5">{{ $sections[2]['title'] }}</h2>
        </div>

        <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4 mb-5">
            <p>
                @foreach($sections[2]['helperTexts'] as $helperText)
                    <li class="text-base text-gray-400">
                        {{ $helperText }}
                    </li>
                @endforeach
            </p>
        </div>
    </div>

</x-filament-panels::page>
