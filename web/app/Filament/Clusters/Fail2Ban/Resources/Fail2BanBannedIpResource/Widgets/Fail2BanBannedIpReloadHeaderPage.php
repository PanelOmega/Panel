<?php

namespace App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class Fail2BanBannedIpReloadHeaderPage extends Widget
{
    protected static string $view = 'filament.admin.components.fail2ban.fail2-ban-banned-ip-reload-header-page';

    public function render(): View
    {
        return view(static::$view);
    }
}
