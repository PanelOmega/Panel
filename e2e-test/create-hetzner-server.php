<?php

require_once __DIR__.'/vendor/autoload.php';

$apiKey = env('HETZNER_API_KEY');

$serverNamePrefix = 'omega-test-commit-';
$serverName = $serverNamePrefix . rand(1000, 9999);

$hetznerClient = new \LKDev\HetznerCloud\HetznerAPIClient($apiKey);
foreach ($hetznerClient->servers()->all() as $server) {
    if (str_contains($server->name, $serverNamePrefix)) {
        $getServer = $hetznerClient->servers()->get($server->id);
        $getServer->delete();
        echo 'Deleting server: '.$server->name.PHP_EOL;
        continue;
    }
    echo 'ID: '.$server->id.' Name:'.$server->name.' Status: '.$server->status.PHP_EOL;
}

$serverType = $hetznerClient->serverTypes()->get(1);
$location = $hetznerClient->locations()->getByName('fsn1');
$image = $hetznerClient->images()->getByName('ubuntu-22.04');
$apiResponse = $hetznerClient->servers()->createInLocation($serverName, $serverType, $image, $location);
$server = $apiResponse->getResponsePart('server');
$action = $apiResponse->getResponsePart('action');
$nextActions = $apiResponse->getResponsePart('next_actions');
echo 'Server: '.$server->name.PHP_EOL;
echo 'IP: '.$server->publicNet->ipv4->ip.PHP_EOL;
echo 'Password: '.$apiResponse->getResponsePart('root_password').PHP_EOL;
echo 'Now we wait on the success of the server creation!'.PHP_EOL;
echo date('H:i:s').PHP_EOL;
$action->waitUntilCompleted();
foreach ($nextActions as $nextAction) {
    $nextAction->waitUntilCompleted();
}
echo date('H:i:s').PHP_EOL;
echo 'Done!';
