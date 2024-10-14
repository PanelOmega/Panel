<x-filament-panels::page>
    <div>

        <p class="text-base text-gray-400 mb-5">
            {{ $sections['title_text']['current_path']['default'] }} @if($dnssecEnabled)
                {{ $sections['title_text']['current_path']['dnssec'] }}
            @elseif($dnssecGenerateEnabled)
                {{ $sections['title_text']['current_path']['dnssec'] }} {{ $sections['title_text']['current_path']['dnssec_generate']}}
            @elseif($manageZonesEnabled)
                {{ $sections['title_text']['current_path']['manage_zone'] }}
            @endif
        </p>
        <p class="text-base text-gray-400 mb-5">
            {{ $sections['title_text'][0] }}
        </p>
        <div class="mt-6">
            <h2 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle'][0] }}</h2>
        </div>
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
