<?php

namespace App\Server\Installers\DNS;

use App\Server\Helpers\OS;

class Bind9Installer
{
    public string $logPath = '/var/log/omega/bind-9-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public static function isBind9Installed()
    {
        $os = OS::getDistro();

        if ($os === OS::DEBIAN || $os === OS::UBUNTU) {
            $command = 'apt list installed | grep bind';
        } elseif ($os === OS::CLOUD_LINUX || $os === OS::CENTOS || $os === OS::ALMA_LINUX) {
            $command = 'yum list installed | grep bind';
        }

        $isInstalled = shell_exec($command);

        return [
            'status' => $isInstalled !== null ? 'success' : 'error',
            'message' => $isInstalled !== null ? 'Bind9 is installed.' : 'Bind9 is not installed.'
        ];
    }

    public function commands()
    {
        $os = OS::getDistro();
        $commands = [];

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt-get install bind9 bind9utils bind9-doc -y';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS
            || $os == OS::ALMA_LINUX
        ) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install bind bind-utils -y';
        }

        $commands[] = 'systemctl enable named';
        $commands[] = 'systemctl start named';
        $commands[] = 'mkdir -p /var/log/named';
        $commands[] = 'touch /var/log/named/default.log';
        $commands[] = 'chown named:named /var/log/named/default.log';
        $commands[] = 'chmod 644 /var/log/named/default.log';
        $commands[] = 'omega-shell omega:update-bind9';
        $commands[] = 'chown named:named /etc/named.conf';

        $isFirewalldEnabled = shell_exec('systemctl is-enabled firewalld');

        if ($isFirewalldEnabled !== 'enabled') {
            $commands[] = 'systemctl enable firewalld';
            $commands[] = 'systemctl start firewalld';
        }

        $commands[] = 'firewall-cmd --add-service=dns --zone=public --permanent';
        $commands[] = 'firewall-cmd --zone=public --add-port=53/tcp --permanent';
        $commands[] = 'firewall-cmd --reload';

        return $commands;

    }

    public function run() {

        $commands = $this->commands();

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'systemctl is-active named ';
        $shellFileContent .= '&& echo "Bind9 installed successfully!" ';
        $shellFileContent .= '|| "Bind9 failed to start!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/bind-9-installer.sh';

        file_put_contents('/tmp/bind-9-installer.sh', $shellFileContent);
        if (!is_dir(dirname($this->logPath))) {
            $command = 'mkdir -p ' . dirname($this->logPath);
            shell_exec($command);
        }

        $command = "bash /tmp/bind-9-installer.sh >> {$this->logPath} 2>&1 &";
        shell_exec($command);

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'Bind9 is being installed and configured. Please check the log file for more details.',
            'logPath' => $this->logPath,
        ];
    }

}
