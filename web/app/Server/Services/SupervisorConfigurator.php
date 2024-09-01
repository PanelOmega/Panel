<?php

namespace App\Server\Services;

use App\Server\Helpers\OS;

class SupervisorConfigurator
{

    public function configure()
    {
        $os = OS::getDistro();

        // Overwrite supervisor config file
        $workersCount = (int) setting('general.supervisor_workers_count', 4);
        $supervisorConf = view('server.samples.supervisor.supervisor-conf', [
            'workersCount' => $workersCount
        ])->render();



        // Overwrite supervisor config file


        if ($os == OS::ALMA_LINUX || $os == OS::CENTOS || $os == OS::CLOUD_LINUX) {
            file_put_contents('/etc/supervisord.d/omega.ini', $supervisorConf);
        } else {
            file_put_contents('/etc/supervisor/conf.d/omega.conf', $supervisorConf);
        }

        // Restart supervisor
        shell_exec('systemctl restart supervisord');

        // Check supervisor config file
        $checkSupervisorStatus = shell_exec('systemctl status supervisord');
        if (strpos($checkSupervisorStatus, 'active (running)') !== false) {
            return [
                'success' => 'Supervisor has been configured successfully'
            ];
        } else {
            return [
                'error' => 'Supervisor is not running. Please check supervisor status'
            ];
        }

    }

}
