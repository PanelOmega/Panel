<?php

namespace App\Server\Installers\Postfix;

use App\Server\Helpers\OS;

class PostfixInstaller
{
    public $logPath = '/var/log/omega/postfix-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public function isPostfixInstalled()
    {
        $os = OS::getDistro();

        if ($os === OS::DEBIAN || $os === OS::UBUNTU) {
            $command = 'apt list installed | grep postfix';
        } elseif ($os === OS::CLOUD_LINUX || $os === OS::CENTOS || $os === OS::ALMA_LINUX) {
            $command = 'yum list installed | grep postfix';
        }

        $isInstalled = shell_exec($command);

        return [
            'status' => $isInstalled !== null ? 'success' : 'error',
            'message' => $isInstalled !== null ? 'Postfix is installed.' : 'Postfix is not installed.'
        ];
    }

    public function run()
    {
        $os = OS::getDistro();
        $commands = [];

        $commands[] = 'echo "Installing postfix..."';

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt-get install postfix -y';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install postfix -y';
        }

        $commands[] = 'systemctl enable postfix';
        $commands[] = 'systemctl start postfix';

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'echo "All packages installed successfully!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/dovecot-installer.sh';

        file_put_contents('/tmp/dovecot-installer.sh', $shellFileContent);

        shell_exec('bash /tmp/dovecot-installer.sh >> ' . $this->logPath . ' &');
    }
}
