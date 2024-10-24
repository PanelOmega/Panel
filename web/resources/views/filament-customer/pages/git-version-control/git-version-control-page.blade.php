<x-filament-panels::page>
    <div>
        <div class="mb-6 bg-red-100 p-4 rounded">
            <p class="text-base text-gray-400 mb-5">
                {!! $sections['warning_text'] !!}
            </p>
        </div>

        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections['title_text'] }}
            </p>
        </div>

        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
