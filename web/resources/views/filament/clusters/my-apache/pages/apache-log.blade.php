<div>
<x-filament-panels::page>

    <div>
        <div id="js-log" wire:poll="pullLog" class="leading-10 text-left text-sm font-medium text-gray-950 dark:text-yellow-500 h-[20rem] overflow-x-hidden overflow-y-scroll">

            @if ($this->loading)
                <x-filament::loading-indicator class="h-12 w-12" />
            @endif

            {!! $this->log !!}

        </div>

        <script>
            window.setInterval(function() {
                var elem = document.getElementById('js-log');
                elem.scrollTop = elem.scrollHeight;
            }, 3000);
        </script>
    </div>

</x-filament-panels::page>
</div>
