<?php

namespace App\Server\Installers\MySQL;

use App\Server\Helpers\OS;

class MariaDBInstaller
{
    public $logPath = '/var/log/omega/mariadb-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public function isMySqlInstalled()
    {
        $os = OS::getDistro();

        if ($os === OS::DEBIAN || $os === OS::UBUNTU) {
            $commands = 'apt list installed | grep mariadb-devel';
        } elseif ($os === OS::CLOUD_LINUX || $os === OS::CENTOS || $os === OS::ALMA_LINUX) {
            $command = 'yum list installed | grep mariadb-devel';
        }

        $isInstalled = shell_exec($command);

        return [
            'status' => $isInstalled !== null ? 'success' : 'error',
            'message' => $isInstalled !== null ? 'MariaDB is installed.' : 'MariaDB is not installed.'
        ];
    }

    public function run()
    {
        $os = OS::getDistro();
        $commands = [];

        $commands[] = 'echo "Installing mariadb..."';

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt-get install libmariadb-dev default-libmysqlclient-dev opendbx -y';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install mariadb-devel mariadb-server opendbx opendbx-mysql -y';
        }

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'echo "All packages installed successfully!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/mariadb-installer.sh';

        file_put_contents('/tmp/mariadb-installer.sh', $shellFileContent);

        shell_exec('bash /tmp/mariadb-installer.sh >> ' . $this->logPath . ' &');
    }
}
