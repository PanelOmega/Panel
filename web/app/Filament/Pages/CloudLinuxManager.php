<?php

namespace App\Filament\Pages;

use App\Server\Installers\CloudLinux\CloudLinuxInstaller;
use Filament\Pages\Page;

class CloudLinuxManager extends Page
{

    protected static ?string $navigationGroup = 'CloudLinux';

//    protected static ?string $navigationIcon = 'omega-cloudlinux';

    protected static string $view = 'filament.admin.pages.cloudlinux-manager';

    public function mount()
    {
        $checkIsInstalled = CloudLinuxInstaller::isCloudLinuxInstalled();
        if ($checkIsInstalled['status'] !== 'success') {
            return $this->redirect(route('filament.admin.pages.cloud-linux-installer'));
        }
    }

}
