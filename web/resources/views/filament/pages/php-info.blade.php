<x-filament-panels::page>

    <div class="grid grid-cols-2 gap-x-8">
        <div>
            @if (!empty($installedPHPVersions))
                <div>
                    Installed PHP Versions on server are:
                </div>
                <div class="flex flex-col gap-y-4 mt-4">
                    @foreach($installedPHPVersions as $phpVersion)
                        <x-filament::section>
                            <div x-data="{showModules:false}">
                                <div class="flex gap-4">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 text-primary-500" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M12 5.601h-.116c-1.61 0-3.18.175-4.69.507l.144-.027a16.125 16.125 0 0 0-3.91 1.343l.094-.042a8.123 8.123 0 0 0-2.57 1.93l-.007.008A3.6 3.6 0 0 0 0 11.684v.004c.019.914.374 1.741.946 2.367l-.002-.003a8.105 8.105 0 0 0 2.529 1.917l.048.021a15.7 15.7 0 0 0 3.71 1.282l.106.019c1.366.305 2.936.48 4.546.48h.123H12h.116c1.61 0 3.18-.175 4.69-.507l-.144.027a16.125 16.125 0 0 0 3.91-1.343l-.094.042a8.123 8.123 0 0 0 2.57-1.93l.007-.008A3.6 3.6 0 0 0 24 11.688v-.004a3.608 3.608 0 0 0-.947-2.371l.002.003a8.105 8.105 0 0 0-2.529-1.917l-.048-.021a15.7 15.7 0 0 0-3.71-1.282l-.106-.019a21.212 21.212 0 0 0-4.546-.48h-.123h.006zm-3.12 7.264c-.131.119-.28.221-.442.301l-.011.005a2.916 2.916 0 0 1-.482.179l-.021.005a1.723 1.723 0 0 1-.579.099h-.024h.001H5.35l-.32 1.963H3.583l1.28-6.675h2.773l.062-.001c.36 0 .706.063 1.026.179l-.021-.007c.295.108.546.276.748.489l.001.001c.175.223.3.493.354.789l.002.011a2.932 2.932 0 0 1-.015 1.059l.003-.019a2.82 2.82 0 0 1-.142.485l.007-.019q-.086.221-.184.417q-.122.196-.27.393a2.164 2.164 0 0 1-.317.343l-.003.002zm4.172.589l.565-2.822c.024-.107.038-.229.038-.355l-.002-.078v.004a.426.426 0 0 0-.111-.283a.671.671 0 0 0-.241-.134l-.005-.001a1.388 1.388 0 0 0-.418-.062l-.051.001h.002h-1.126l-.736 3.73H9.544l1.28-6.48h1.423l-.343 1.767h1.28l.073-.001c.331 0 .653.041.961.117l-.027-.006c.249.055.466.172.641.332l-.001-.001a.84.84 0 0 1 .306.498l.001.005a1.945 1.945 0 0 1-.04.787l.003-.014l-.589 2.994zm7.902-2.184c-.04.181-.082.328-.132.473l.009-.031c-.054.159-.12.297-.201.425l.005-.008a1.812 1.812 0 0 1-.248.408l.003-.004c-.098.122-.203.23-.317.329l-.003.003c-.131.119-.28.221-.442.301l-.011.005a2.916 2.916 0 0 1-.482.179l-.021.005a1.723 1.723 0 0 1-.579.099h-.024h.001h-1.972l-.343 1.959h-1.423l1.28-6.675h2.749l.073-.001c.365 0 .716.063 1.041.18l-.022-.007c.287.104.529.272.718.488l.002.002c.19.222.325.497.378.799l.002.01a2.763 2.763 0 0 1-.04 1.076l.004-.019zm-2.7-1.547h-.978l-.513 2.749h.908c.25 0 .496-.023.734-.066l-.025.004c.204-.036.386-.109.546-.212l-.006.003c.136-.122.25-.263.339-.421l.004-.008c.103-.188.18-.407.219-.638l.002-.012a1.877 1.877 0 0 0 .036-.649l.001.009a.812.812 0 0 0-.161-.419l.001.002a1.116 1.116 0 0 0-.409-.243l-.008-.002a1.982 1.982 0 0 0-.689-.096h.003zm-11.19 0h-.978l-.515 2.749h.91c.25 0 .496-.023.734-.066l-.025.004c.204-.036.386-.109.546-.212l-.006.003c.136-.122.25-.263.339-.421l.004-.008c.103-.188.18-.407.219-.638l.002-.012a1.877 1.877 0 0 0 .036-.649l.001.009a.812.812 0 0 0-.161-.419l.001.002a1.116 1.116 0 0 0-.409-.243l-.008-.002a1.982 1.982 0 0 0-.689-.096h.003z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            PHP {{ $phpVersion['version'] }}
                                        </div>
                                        <div class="text-sm">
                                            <a href="#" x-on:click="showModules = ! showModules">
                                                <span x-show="showModules">Hide modules</span>
                                                <span x-show="!showModules">Show modules</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="showModules" class="text-sm">
                                    @foreach($phpVersion['modules'] as $phpModule)
                                        {{ $phpModule }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </div>
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
            @else
                No PHP versions found.
            @endif
        </div>

        <div class="">
            <x-filament::section>
                <div class="text-center">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="text-primary-500 w-[4rem] m-auto" viewBox="0 0 512 512">
                            <path fill="currentColor" d="M170.322 349.808c-2.4-15.66-9-28.38-25.02-34.531c-6.27-2.4-11.7-6.78-17.88-9.54c-7.02-3.15-14.16-6.15-21.57-8.1c-5.61-1.5-10.83 1.02-14.16 5.94c-3.15 4.62-.87 8.97 1.77 12.84c2.97 4.35 6.27 8.49 9.6 12.57c5.52 6.78 11.37 13.29 16.74 20.161c5.13 6.57 9.51 13.86 8.76 22.56c-1.65 19.08-10.29 34.891-24.21 47.76c-1.53 1.38-4.23 2.37-6.21 2.19c-8.88-.96-16.95-4.32-23.46-10.53c-7.47-7.11-6.33-15.48 2.61-20.67c2.13-1.23 4.35-2.37 6.3-3.87c5.46-4.11 7.29-11.13 4.32-17.22c-1.41-2.94-3-6.12-5.34-8.25c-11.43-10.41-22.651-21.151-34.891-30.63C18.01 307.447 2.771 276.968.43 240.067c-2.64-40.981 6.87-79.231 28.5-114.242c8.19-13.29 17.73-25.951 32.37-32.52c9.96-4.47 20.88-6.99 31.531-9.78c29.311-7.71 58.89-13.5 89.401-8.34c26.28 4.41 45.511 17.94 54.331 43.77c5.79 16.89 7.17 34.35 5.37 52.231c-3.54 35.131-29.49 66.541-63.331 75.841c-14.67 4.02-22.68 1.77-31.5-10.44c-6.33-8.79-11.58-18.36-17.25-27.631c-.84-1.38-1.44-2.97-2.16-4.44c-.69-1.47-1.44-2.88-2.16-4.35c2.13 15.24 5.67 29.911 13.98 42.99c4.5 7.11 10.5 12.36 19.29 13.14c32.34 2.91 59.641-7.71 79.021-33.721c21.69-29.101 26.461-62.581 20.19-97.831c-1.23-6.96-3.3-13.77-4.77-20.7c-.99-4.47.78-7.77 5.19-9.33c2.04-.69 4.14-1.26 6.18-1.68c26.461-5.7 53.221-7.59 80.191-4.86c30.601 3.06 59.551 11.46 85.441 28.471c40.531 26.67 65.641 64.621 79.291 110.522c1.98 6.66 2.28 13.95 2.46 20.971c.12 4.68-2.88 5.91-6.45 2.97c-3.93-3.21-7.53-6.87-10.92-10.65c-3.15-3.57-5.67-7.65-8.73-11.4c-2.37-2.94-4.44-2.49-5.58 1.17c-.72 2.22-1.35 4.41-1.98 6.63c-7.08 25.26-18.24 48.3-36.33 67.711c-2.52 2.73-4.77 6.78-5.07 10.38c-.78 9.96-1.35 20.13-.39 30.06c1.98 21.331 5.07 42.57 7.47 63.871c1.35 12.03-2.52 19.11-13.83 23.281c-7.95 2.91-16.47 5.04-24.87 5.64c-13.38.93-26.88.27-40.32.27c-.36-15 .93-29.731-13.17-37.771c2.73-11.13 5.88-21.69 7.77-32.49c1.56-8.97.24-17.79-6.06-25.14c-5.91-6.93-13.32-8.82-20.101-4.86c-20.43 11.91-41.671 11.97-63.301 4.17c-9.93-3.6-16.86-1.56-22.351 7.5c-5.91 9.75-8.4 20.7-7.74 31.771c.84 13.95 3.27 27.75 5.13 41.64c1.02 7.77.15 9.78-7.56 11.76c-17.13 4.35-34.56 4.83-52.081 3.42c-.93-.09-1.86-.48-2.46-.63c-.87-14.55.66-29.671-16.68-37.411c7.68-16.29 6.63-33.18 3.99-50.07l-.06-.15zm-103.561-57.09c2.55-2.4 4.59-6.15 5.31-9.6c1.8-8.64-4.68-20.22-12.18-23.43c-3.99-1.74-7.47-1.11-10.29 2.07c-6.87 7.77-13.65 15.63-20.401 23.521c-1.14 1.35-2.16 2.94-2.97 4.53c-2.7 5.19-1.11 8.97 4.65 10.38c3.48.87 7.08 1.05 10.65 1.56c9.3-.9 18.3-2.46 25.23-9zm.78-86.371c-.03-6.18-5.19-11.34-11.28-11.37c-6.27-.03-11.67 5.58-11.46 11.76c.27 6.21 5.43 11.19 11.61 11.07c6.24-.09 11.22-5.19 11.16-11.43z" />
                        </svg>
                    </div>
                    Do you want to install another php version ?
                    <x-filament::link :href="route('filament.admin.pages.php-installer')">
                        Click here to install
                    </x-filament::link>
                </div>
            </x-filament::section>
        </div>

    </div>

</x-filament-panels::page>
