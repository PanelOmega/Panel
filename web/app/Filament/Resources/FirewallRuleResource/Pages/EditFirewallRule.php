<?php

namespace App\Filament\Resources\FirewallRuleResource\Pages;

use App\Filament\Resources\FirewallRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFirewallRule extends EditRecord
{
    protected static string $resource = FirewallRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
