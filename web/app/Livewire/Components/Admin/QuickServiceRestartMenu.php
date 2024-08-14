<?php

namespace App\Livewire\Components\Admin;

use App\Server\Helpers\OS;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use Livewire\Component;

class QuickServiceRestartMenu extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function restartApache()
    {
        shell_exec('sudo service apache2 restart');
    }

    public function restartSupervisor()
    {
        $os = OS::getDistro();
        if ($os == OS::CLOUD_LINUX || $os == OS::ALMA_LINUX) {
            shell_exec('sudo systemctl supervisord restart');
            return;
        } else {
            shell_exec('sudo service supervisor restart');
        }
    }

    public function restartMysql()
    {
        shell_exec('sudo service omega restart');
    }

    public function restartFtp()
    {
        shell_exec('sudo systemctl restart vsftpd');
    }

    public function restartOmegaServices()
    {
        shell_exec('sudo service omega restart');
    }

    public function render(): View
    {
        return view('filament.components.quick-service-restart-menu');
    }
}
