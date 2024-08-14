<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FirewalldBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;

    public function __construct($fixPermissions = false)
    {
        $this->fixPermissions = $fixPermissions;
    }

    public function handle(): void {

        $command = 'systemctl is-active firewalld';
        $isFirewalldActive = trim(shell_exec($command));

        if($isFirewalldActive !== 'active') {

            $commands = [
                'systemctl start firewalld',
                'systemctl enable firewalld',
            ];

            foreach($commands as $command) {
                shell_exec($command);
            }
        }


        $requiredPorts = [
            '8443/tcp',
            '443/tcp',
            '80/tcp'
        ];

        $openPorts = shell_exec('firewall-cmd --list-ports');

        foreach ($requiredPorts as $port) {
            if (strpos($openPorts, $port) === false) {
                shell_exec("firewall-cmd --permanent --add-port={$port}");
            }
        }

        shell_exec('firewall-cmd --reload');
    }

}
