<div>
<x-filament-panels::page>

    <script src="https://cdn.tailwindcss.com"></script>

    @php
    $tabs = [
        [
            'name' => 'Apache MPM',
            'component' => \App\Livewire\Components\Admin\MyApache\MyApacheMPMModulesTable::class,
        ],
        [
            'name' => 'Apache Modules',
            'component' => \App\Livewire\Components\Admin\MyApache\MyApacheModulesTable::class,
        ],
        [
            'name' => 'PHP Versions',
            'component' => \App\Livewire\Components\Admin\MyApache\MyApachePHPVersionsTable::class,
        ],
        [
            'name' => 'PHP Extensions',
            'component' => \App\Livewire\Components\Admin\MyApache\MyApachePHPExtensionsTable::class,
        ],
        [
            'name' => 'Review',
            'component' => \App\Livewire\Components\Admin\MyApache\MyApacheReview::class,
        ]
    ];
    @endphp

    <div class="flex gap-8"
        x-data="{
            activeTab: 0,
            setActiveTab(index) {
                this.activeTab = index;
            }
        }"
    >
        <div>
            <ol role="list" class="grid divide-y divide-gray-200 dark:divide-white/5 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

             @foreach($tabs as $tab)
            <li class="relative flex active">
                <button type="button"
                        x-on:click="setActiveTab({{ $loop->index }})"
                        class="cursor-pointer flex h-full items-center gap-x-4 px-6 py-4 text-start">
                    <div class="grid justify-items-start md:w-max md:max-w-60">
                        <span class="text-sm font-medium"
                                :class="{ ' text-primary-600 dark:text-primary-400 ': activeTab == {{ $loop->index }}, ' ': activeTab != {{ $loop->index }} }"
                        >
                            {{ $tab['name'] }}
                        </span>
                    </div>
                </button>
            </li>
            @endforeach

            </ol>
        </div>

        <div class="w-full">
            @foreach($tabs as $tab)
                <div x-show="activeTab == '{{ $loop->index }}'">
                    <div>
                        <h2 class="text-2xl font-bold">{{ $tab['name'] }}</h2>
                    </div>
                    <div class="mt-4">
                        @livewire($tab['component'])
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <div>
     {{--   <form wire:submit="provision">
            {{ $this->form }}
        </form>--}}

        <x-filament-actions::modals />
    </div>

</x-filament-panels::page>
</div>
