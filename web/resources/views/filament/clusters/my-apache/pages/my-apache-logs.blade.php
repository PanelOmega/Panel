<div>
<x-filament-panels::page>


    <div class="flex gap-4 justify-between mb-4">
        <div>

            <x-filament::tabs label="Apache Logs">

                <x-filament::tabs.item
                    :active="$logName === 'error_log'"
                    wire:click="switchLog('error_log')"
                >
                    Error Log
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$logName === 'access_log'"
                    wire:click="switchLog('access_log')"
                >
                    Access Log
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$logName === 'suexec_log'"
                    wire:click="switchLog('suexec_log')"
                >
                    Suexec Log
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>
        <div>
            <x-filament::button wire:click="clearLog">
                Clear {{str_replace('_', ' ', $logName)}}
            </x-filament::button>
        </div>
    </div>

    <div>

        <div id="js-log" wire:poll="pullLog" class="bg-gray-50 dark:bg-[#161719] p-4 rounded-md leading-10 text-left text-sm font-medium text-gray-950 dark:text-[#E0E0E0] h-screen overflow-x-hidden overflow-y-scroll">

            @if ($this->loading)
                <div class="flex gap-2 items-center">
                    <x-filament::loading-indicator class="h-6 w-6" /> Loading...
                </div>
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


                // ip address
                $log = preg_replace('/([0-9]{1,3}\.){3}[0-9]{1,3}/', '<span class="text-blue-500">$0</span>', $log);


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
