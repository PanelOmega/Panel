<?php

namespace App\Filament\Resources\FirewallRuleResource\Pages;

use App\Filament\Resources\FirewallRuleResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListFirewallRules extends ManageRecords
{
    protected static string $resource = FirewallRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
          //  'all' => Tab::make(),
            'ipv4' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('protocol', 'ipv4')),
            'ipv6' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('protocol', 'ipv6')),
        ];
    }
}
