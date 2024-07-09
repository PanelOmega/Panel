<?php

namespace App\Server\Installers\CloudLinux;

use App\Server\Helpers\OS;

class CloudLinuxInstaller
{
    public string $logPath = '/var/log/omega/cloudlinux-installer.log';

    public function run($activationKey)
    {
        if (empty($activationKey)) {
            return [
                'status' => 'error',
                'message' => 'Activation key is required.',
            ];
        }

        $os = OS::getDistro();

        $commands = [];

        if ($os == OS::ALMA_LINUX) {
            $commands[] = 'yum install wget -y';
            $commands[] = 'wget https://repo.cloudlinux.com/cloudlinux/sources/cln/cldeploy';
            $commands[] = 'bash cldeploy -k '.$activationKey;
        }

        $commands[] = 'mkdir -p /usr/local/omega/web/public/thirdparty/cloudlinux';
        $commands[] = 'ln -s /usr/share/l.v.e-manager/commons/spa-resources/ /usr/local/omega/web/public/thirdparty/cloudlinux';

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command.PHP_EOL;
        }

        $shellFileContent .= 'echo "CloudLinux is installed successfully!"'.PHP_EOL;
        $shellFileContent .= 'echo "DONE!"'.PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/cloudlinux-installer.sh';

        file_put_contents('/tmp/cloudlinux-installer.sh', $shellFileContent);

        if (! is_dir(dirname($this->logPath))) {
            shell_exec('mkdir -p '.dirname($this->logPath));
        }

        shell_exec('bash /tmp/cloudlinux-installer.sh >> '.$this->logPath.' &');

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'CloudLinux is being installed in the background. Please check the log file for more details.',
            'logPath' => $this->logPath,
        ];
    }

    public function installNodeJSSelector()
    {
        $commands = [];
        $commands[] = 'yum groupinstall alt-nodejs -y';
        $commands[] = 'yum install lvemanager lve-utils-y';
        $commands[] = 'cloudlinux-selector set --json --interpreter nodejs --selector-status enabled';
    }

}
