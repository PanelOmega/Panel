<?php

namespace App\Server\Installers\DNS;

use App\Server\Helpers\OS;

class PowerDnsInstaller
{
    public string $logPath = '/var/log/omega/power-dns-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public static function isPowerDnsInstalled()
    {
        $os = OS::getDistro();

        if ($os === OS::DEBIAN || $os === OS::UBUNTU) {
            $command = 'apt list installed | grep pdns';
        } elseif ($os === OS::CLOUD_LINUX || $os === OS::CENTOS || $os === OS::ALMA_LINUX) {
            $command = 'yum list installed | grep pdns';
        }

        $isInstalled = shell_exec($command);

        return [
            'status' => $isInstalled !== null ? 'success' : 'error',
            'message' => $isInstalled !== null ? 'PowerDNS is installed.' : 'PowerDNS is not installed.'
        ];
    }

    public function run()
    {
        $os = OS::getDistro();
        $commands = [];

        if ($os === OS::DEBIAN || $os === OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt install pdns-server pdns-backend-mysql sqlite3 -y';
//            $commands[] = 'apt install php php-gd php-imap php-ldap php-mysql php-odbc php-xml php-xmlrpc php-mbstring php-mcrypt php-mhash gettext php-pear sqlite3 -y';
        } elseif ($os === OS::CLOUD_LINUX || $os === OS::CENTOS || $os === OS::ALMA_LINUX) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install pdns pdns-backend-mysql sqlite -y';
//            $commands[] = 'yum install php php-devel php-gd php-imap php-ldap php-mysql php-odbc php-pear php-xml php-xmlrpc php-mbstring php-mcrypt php-mhash php-pear sqlite -y';
        }

        $commands[] = 'systemctl enable pdns';
        $commands[] = 'systemctl start pdns';
        $commands[] = 'chown named:named /etc/pdns/pdns.conf';

        if (!is_dir('/var/omega/pdns')) {
            mkdir('/var/omega/pdns');
        }

        $commands[] = 'chown -R named:named /var/omega/pdns';
        $commands[] = 'pdnsutil create-bind-db /var/omega/pdns/dnssec.db';
        $commands[] = 'omega-shell omega:update-pdns-config';
        $commands[] = 'omega-shell omega:update-pdns-service';
        $commands[] = 'systemctl daemon-reload';
        $commands[] = 'systemctl restart pdns';

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'systemctl is-active pdns ';
        $shellFileContent .= '&& echo "PowerDNS installed successfully!" ';
        $shellFileContent .= '|| "PowerDNS failed to start!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/power-dns-installer.sh';

        file_put_contents('/tmp/power-dns-installer.sh', $shellFileContent);

        if (!is_dir(dirname($this->logPath))) {
            $command = 'mkdir -p ' . dirname($this->logPath);
            shell_exec($command);
        }
        $command = "bash /tmp/power-dns-installer.sh >> {$this->logPath} 2>&1 &";
        shell_exec($command);


        return [
            'status' => 'Install job is running in the background.',
            'message' => 'PowerDNS is being installed and configured. Please check the log file for more details.',
            'logPath' => $this->logPath
        ];
    }
}
