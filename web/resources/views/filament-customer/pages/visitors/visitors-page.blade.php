<x-filament-panels::page>
    <div>
        @if(!$this->domainView)
        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                    {{ $sections['section_title']['defaultView'] }}
            </p>
            <div class="mb-10 mt-6 mx-4">
                <h1 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle'] }}
                </h1>
            </div>
        </div>
            @else
            <div class="mb-6">
                <p class="text-base text-gray-400">
                {{ $sections['section_title']['domainView']['domain'] }} {{ $this->currentDomain }}
                </p>
                @if($this->reportedPeriod !== ' - ')
                    <p class="text-base text-gray-400">
                    {!! $sections['section_title']['domainView']['period'] !!} {{ $this->reportedPeriod }}
                    </p>
                    <p class="text-base text-gray-400">
                    {!! $sections['section_title']['domainView']['data'] !!} {{ $this->totalDataSent }}
                    </p>
                @endif
            </div>

        @endif

        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
