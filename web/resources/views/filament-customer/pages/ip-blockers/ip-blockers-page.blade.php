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

        <div class="mt-6">
            {{ $this->form }}
        </div>

        <div class="mt-6">
            <p class="text-base text-gray-400 mb-5">
                <strong>{{ $sections['note']['section_note_title'][0] }}</strong>{{ $sections['note']['section_note_title'][1] }}
            </p>
            @foreach($sections['note']['section_note_text'] as $key => $text)
                <p class="text-base text-gray-400">
                    @foreach($text as $sectionText)
                        @if ($loop->first)
                            <strong>{{ $sectionText }}</strong><br>
                        @else
                            {{ $sectionText }}<br>
                        @endif
                    @endforeach
                </p>
            @endforeach
        </div>

        <div class=" mb-10 mt-6 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections['section_subtitle'][0] }}</h1>
        </div>

        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>

</x-filament-panels::page>
