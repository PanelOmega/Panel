<?php

namespace App\FilamentCustomer\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class FtpConnectionsHeaderWidget extends Widget
{
    protected static string $view = 'filament.customer.components.ftp_connections.ftp-connections-list-page';

    public function render(): View
    {
        return view(static::$view);
    }
}
