<?php
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::any('cloud-linux/send-request', function () {

    require_once('/usr/share/l.v.e-manager/panelless-version/lvemanager/LveManager.php');

    $integrationIniFile = '/opt/cpvendor/etc/integration.ini';
    if (!file_exists($integrationIniFile)) {
        shell_exec('mkdir -p /opt/cpvendor/etc');
        $integrationIniContent = '
[lvemanager_config]
# Required
ui_user_info = /usr/local/omega/web/ui_user_info.sh

#
ui_user_info script
        ';

        file_put_contents($integrationIniFile, $integrationIniContent);
    }

    $manager = new LveManager();
    return $manager->processRequest(LveManager::OWNER_ADMIN);

})->middleware([

]);
