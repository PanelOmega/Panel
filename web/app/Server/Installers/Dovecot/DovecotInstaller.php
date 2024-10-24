<?php

namespace App\Server\Installers\Dovecot;

use App\Server\Helpers\OS;

class DovecotInstaller
{

    public $logPath = '/var/log/omega/devocot-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public function isDovecotInstalled()
    {
        $os = OS::getDistro();

        if ($os === OS::DEBIAN || $os === OS::UBUNTU) {
            $command = 'apt list installed | grep dovecot';
        } elseif ($os === OS::CLOUD_LINUX || $os === OS::CENTOS || $os === OS::ALMA_LINUX) {
            $command = 'yum list installed | grep dovecot';
        }

        $isInstalled = shell_exec($command);

        return [
            'status' => $isInstalled !== null ? 'success' : 'error',
            'message' => $isInstalled !== null ? 'Dovecot is installed.' : 'Dovecot is not installed.'
        ];
    }

    public function commands()
    {
        $os = OS::getDistro();
        $commands = [];

        $commands[] = 'echo "Installing dovecot..."';

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt-get install telnet exim4 dovecot-core dovecot-imapd dovecot-pop3d dovecot-lmtpd -yq';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS
            || $os == OS::ALMA_LINUX
        ) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install telnet exim dovecot -y';
        }

        $commands[] = 'systemctl enable dovecot';
        $commands[] = 'systemctl start dovecot';
        $commands[] = 'systemctl enable exim';
        $commands[] = 'systemctl start exim';

        return $commands;
    }

    public function run() {

        $commands = $this->commands();

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
