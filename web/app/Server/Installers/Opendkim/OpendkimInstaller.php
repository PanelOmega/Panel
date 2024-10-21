<?php

namespace App\Server\Installers\Opendkim;

use App\Server\Helpers\OS;

class OpendkimInstaller
{
    public $logPath = '/var/log/omega/opendkim-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public function isOpendkimInstalled()
    {
        $os = OS::getDistro();

        if ($os === OS::DEBIAN || $os === OS::UBUNTU) {
            $command = 'apt list installed | grep opendkim';
        } elseif ($os === OS::CLOUD_LINUX || $os === OS::CENTOS || $os === OS::ALMA_LINUX) {
            $command = 'yum list installed | grep opendkim';
        }

        $isInstalled = shell_exec($command);

        return [
            'status' => $isInstalled !== null ? 'success' : 'error',
            'message' => $isInstalled !== null ? 'Opendkim is installed.' : 'Opendkim is not installed.'
        ];
    }

    public function run()
    {
        $os = OS::getDistro();
        $commands = [];

        $commands[] = 'echo "Installing opendkim..."';

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt-get install opendkim opendkim-tools -y';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install opendkim opendkim-tools -y';
        }

        $commands[] = 'systemctl enable opendkim';
        $commands[] = 'systemctl start opendkim';

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'echo "All packages installed successfully!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/opendkim-installer.sh';

        file_put_contents('/tmp/opendkim-installer.sh', $shellFileContent);

        shell_exec('bash /tmp/opendkim-installer.sh >> ' . $this->logPath . ' &');
    }
}
