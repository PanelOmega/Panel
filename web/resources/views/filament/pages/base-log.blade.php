<x-filament-panels::page>

    <div>

        <div id="js-log" wire:poll="pullLog" class="bg-gray-50 dark:bg-[#161719] p-4 rounded-md leading-10 text-left text-sm font-medium text-gray-950 dark:text-[#E0E0E0] h-screen overflow-x-hidden overflow-y-scroll">

            @if ($this->loading)
                <div class="flex gap-2 items-center">
                    <x-filament::loading-indicator class="h-6 w-6" /> Loading...
                </div>
            @endif

            @php
                $log = nl2br($this->log);
            @endphp

            {!! $log !!}

        </div>

        <script>
            window.setInterval(function() {
                var elem = document.getElementById('js-log');
                elem.scrollTop = elem.scrollHeight;
            }, 3000);
        </script>
    </div>

</x-filament-panels::page>
