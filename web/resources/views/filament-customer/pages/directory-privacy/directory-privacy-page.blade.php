<x-filament-panels::page>
    <div>
        <div class="mb-6">
            <p class="mt-4">
                {{ $sections['section'][0] }}
            </p>

            <div x-show="open" class="mt-4">
                <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4">
                    <p>{{ $sections['section'][1] }}</p>
                </div>
            </div>
        </div>
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
