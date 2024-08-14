<div>
<x-filament-panels::page>

    <div>
        <div id="js-log" wire:poll="pullLog" class="bg-gray-50 dark:bg-[#161719] p-4 rounded-md leading-10 text-left text-sm font-medium text-gray-950 dark:text-[#E0E0E0] h-screen overflow-x-hidden overflow-y-scroll">

            @if ($this->loading)
                <x-filament::loading-indicator class="h-12 w-12" />
            @endif


            @php
                $log = nl2br($this->log);
                $log = preg_replace('/\[(.*?)\]:/', '<span class="text-green-500">$0</span>', $log);

                $log = preg_replace('/\[.* [0-9][0-9][0-9][0-9]\]/', '<span class="text-green-500">$0</span>', $log);

                // [negotiation:error]
                $log = preg_replace('/\[negotiation:error\]/', '<span class="text-red-500">$0</span><br />', $log);

                // [core:alert]
                $log = preg_replace('/\[core:alert\]/', '<span class="text-yellow-500">$0</span><br />', $log);

                // [core:crit]
                $log = preg_replace('/\[core:crit\]/', '<span class="text-red-600">$0</span><br />', $log);

                // [core:error]
                $log = preg_replace('/\[core:error\]/', '<span class="text-red-500">$0</span><br />', $log);

                // [core:warn]
                $log = preg_replace('/\[core:warn\]/', '<span class="text-yellow-500">$0</span><br />', $log);

                // [mpm_event:notice]
                $log = preg_replace('/\[mpm_event:notice\]/', '<span class="text-blue-500">$0</span>', $log);

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
</div>
