@php
    $color = \Filament\Support\Colors\Color::Orange;
@endphp

<div
    class="
    flex gap-2
        mt-4 hidden sm:flex items-center py-4 px-3 text-sm font-medium
        rounded-lg shadow-sm ring-1
        ring-yellow-600/20 bg-yellow-50 text-yellow-600
        dark:ring-yellow-400/30 dark:bg-yellow-400/10 dark:text-yellow-400
    "
    style="
        --c-50: {{ $color[50] }};
        --c-300: {{ $color[300] }};
        --c-400: {{ $color[400] }};
        --c-600: {{ $color[600] }};
    "
>

    <svg xmlns="http://www.w3.org/2000/svg" class="w-6" viewBox="0 0 256 256">
        <path fill="currentColor" d="M235.07 189.09L147.61 37.22a22.75 22.75 0 0 0-39.22 0L20.93 189.09a21.53 21.53 0 0 0 0 21.72A22.35 22.35 0 0 0 40.55 222h174.9a22.35 22.35 0 0 0 19.6-11.19a21.53 21.53 0 0 0 .02-21.72m-10.41 15.71a10.46 10.46 0 0 1-9.21 5.2H40.55a10.46 10.46 0 0 1-9.21-5.2a9.51 9.51 0 0 1 0-9.72l87.45-151.87a10.75 10.75 0 0 1 18.42 0l87.46 151.87a9.51 9.51 0 0 1-.01 9.72M122 144v-40a6 6 0 0 1 12 0v40a6 6 0 0 1-12 0m16 36a10 10 0 1 1-10-10a10 10 0 0 1 10 10" />
    </svg>

    Warning: This is a demo of PanelOmega. Any user changes will be lost when the demo is reset.
</div>
