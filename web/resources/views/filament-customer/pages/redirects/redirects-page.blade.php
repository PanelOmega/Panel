<x-filament-panels::page>
    <div>
        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections['section_title'][0] }}
            </p>
        </div>

        <div class="mt-10 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle_add'][0] }}</h1>
        </div>

        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections['section_subtitle_add'][0] }}
            </p>
        </div>

        <div class="mt-6">
            {{ $this->form }}
        </div>

        <div class="mt-6">
            <p class="text-base text-gray-400 mb-5">
                <strong>{{ $sections['note']['section_name'] }}</strong>
            </p>
            <p class="text-base text-gray-400 mb-5">
                @foreach($sections['note']['sections_li'] as $sectionText)
                    <li class="text-base text-gray-400">{{ $sectionText }}</li>
                @endforeach
            </p>
        </div>

        <div class=" mb-10 mt-6 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections['section_subtitle'][0] }}</h1>
        </div>

        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>

</x-filament-panels::page>
