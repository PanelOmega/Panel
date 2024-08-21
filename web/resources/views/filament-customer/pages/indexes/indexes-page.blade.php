<x-filament-panels::page>
    <div>
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
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
