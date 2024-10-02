<x-filament-panels::page>
    <div>
        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {!! $sections['title_text'][0] !!}
            </p>
        </div>

        <div class="mt-6">
            {{ $this->table }}
        </div>

        <div class="mt-3 mb-6 mr-5 text-right">
            <strong>Total Disk Usage: {{ $this->getTotalDiskUsage() }} MB</strong>
        </div>

        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections['title_text'][1] }}
            </p>
        </div>
        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections['title_text'][2] }}
            </p>
        </div>

        <div class="mb-10 mt-6 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle'] }}
            </h1>
        </div>

        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {!! $sections['subtitle_text'] !!}
            </p>
        </div>

    </div>

</x-filament-panels::page>
