<?php

namespace App\Server\Helpers\DNS;

use App\Jobs\ZoneEditorConfigBuild;
use App\Models\Customer;
use App\Models\Domain;

class NameserverSelectionHelper
{
    public static function updateNameserver(string $service)
    {

        $commands = [];
        switch ($service) {
            case 'pdns':
                $commands = [
                    'systemctl stop named',
//                    'omega-shell omega:update-bind9-config',
                    'systemctl restart pdns',
                    "systemctl is-active pdns && echo 'PowerDNS configured and active!' || echo 'There was an issue activating PowerDNS!'"
                ];
                break;

            case 'named':
                $commands = [
                    'systemctl stop pdns',
                    'systemctl restart named',
                    "systemctl is-active named && echo 'Bind9 configured and active!' || echo 'There was an issue activating Bind9!'"
                ];
                break;

            default:
                $commands = [
                    'systemctl stop named',
                    "systemctl is-active named && echo 'Bind9 stopped successfully!' || echo 'Bind9 failed to stop!'",
                    'systemctl stop pdns',
                    "systemctl is-active pdns && echo 'PowerDNS stopped successfully!' || echo 'PowerDNS failed to stop!'"
                ];
                break;
        }

        foreach ($commands as $command) {
            shell_exec($command);
        }

        if($service === 'pdns') {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $zoneBuilder = new ZoneEditorConfigBuild(false, $hostingSubscription);
            $zoneBuilder->udpateConfigs($service);
        }
    }
}
