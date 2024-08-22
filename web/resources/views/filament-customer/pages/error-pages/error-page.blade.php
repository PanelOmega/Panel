<x-filament-panels::page>
    <div>

        <div class="mb-6">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections['section'][0] }}
            </p>
        </div>
        <div class="mb-6 bg-gray-100 dark:bg-gray-800 p-4 mb-5">
            <p class="text-base text-gray-400 mb-5">
                {{ $sections['section'][1] }}
            </p>
        </div>

        <div class="mb-10 mt-6 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle'][0] }}
                <x-heroicon-o-arrow-up class="inline-block w-5 h-5 text-gray-400 mr-2"/>
            </h1>
        </div>

        <div class="mb-10 mt-6 mx-4">
            <h1 class="text-xl text-gray-400 mb-5">{{ $sections['subtitle'][1] }} {{ $domain }}</h1>
        </div>

        <div>
            @foreach ($this->getTabs() as $key => $tab)
                <x-filament::button
                    class="tab-button px-4 py-2 border-b-2
                        {{ $currentTab === $key ? 'border-blue-500' : 'border-transparent' }}"
                    onclick="changeTab('{{ $key }}')">
                    {{ $tab->getLabel() }}
                </x-filament::button>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>

    <script>
        function changeTab(tab) {
            const scrollPosition = window.scrollY;

            history.replaceState({scrollPosition: scrollPosition, tab: tab}, '', `?tab=${tab}`);

            if (!sessionStorage.getItem('hasReloaded')) {
                sessionStorage.setItem('hasReloaded', 'true');
                window.location.reload();
            } else {
                window.scrollTo(0, scrollPosition);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const state = history.state;
            if (state && state.scrollPosition !== undefined) {
                window.scrollTo(0, state.scrollPosition);
            }
            sessionStorage.removeItem('hasReloaded');
        });
    </script>

</x-filament-panels::page>
