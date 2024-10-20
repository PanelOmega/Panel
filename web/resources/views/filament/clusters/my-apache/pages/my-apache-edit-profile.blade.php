<div>
<x-filament-panels::page>

    <div>

        @livewire(\App\Livewire\Components\Admin\MyApache\MyApacheMPMModulesTable::class)
        @livewire(\App\Livewire\Components\Admin\MyApache\MyApacheModulesTable::class)
        @livewire(\App\Livewire\Components\Admin\MyApache\MyApachePHPExtensionsTable::class)

    </div>

    <div>
        <form wire:submit="provision">
            {{ $this->form }}
        </form>

        <x-filament-actions::modals />
    </div>
    
</x-filament-panels::page>
</div>
