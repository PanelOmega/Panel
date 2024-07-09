<?php
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::any('cloud-linux/send-request', function () {

    require_once('/usr/share/l.v.e-manager/panelless-version/lvemanager/LveManager.php');

    $cloudlinuxCli = '/usr/bin/sudo /usr/share/l.v.e-manager/utils/cloudlinux-cli.py';

    $command = request()->get('command');
    $params = request()->get('params');
    $method = request()->get('method');

    $data = [];
    $data['owner'] = 'admin';

    if ($command) {
        $data['command'] = $command;
    }
    if ($params) {
        $data['params'] = $params;
    }
    if ($method) {
        $data['method'] = $method;
    }

    $data['user_info'] = array(
        'userName' => 'admin',
        'userType' => 'admin',
    );

    $fullCommandStr = sprintf(
        "%s --data=%s 2>&1",
        $cloudlinuxCli, base64_encode(json_encode($data))
    );

    putenv('LC_ALL=en_US.UTF-8');

    ob_start();
    passthru($fullCommandStr);
    $responseInJson = ob_get_contents();
    ob_end_clean();

    $response = json_decode($responseInJson, true);

    return response()->json($response);

})->middleware([

]);
