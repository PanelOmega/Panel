<div class="prose mx-4 my-6" x-data="{ open: false }">
    <h1 class="text-4xl font-bold dark:text-white">{{ $sectionData['title'] }}</h1>
    <p class="mt-4">
        {{ $sectionData['section_title'] }}
        <button @click="open = !open" class="underline hover:decoration-dotted">
            <span x-text="open ? 'Hide example index files' : 'Show example index files'"></span>
        </button>
    </p>

    <div x-show="open" class="mt-4">
        <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4">
            <h2 class="text-xl font-semibold dark:text-white">{{ $sectionData['subtitle'] }}</h2>
            <p>{{ $sectionData['section_subtitle'] }}</p>
        </div>
    </div>

    <div class="mb-4">
        @if (isset($headerActions))
            <div class="mb-4">
                @foreach($headerActions as $action)
                    {{ $action }}
                @endforeach
            </div>
        @endif
    </div>
</div>
