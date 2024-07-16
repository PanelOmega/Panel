<?php

namespace App\Server\Installers\CloudLinux;

use App\Server\Helpers\OS;

class CloudLinuxInstaller
{
    public string $logPath = '/var/log/omega/cloudlinux-installer.log';

    public string $activationKey;

    public function setLogPath($path)
    {
        $this->logPath = $path;
    }

    public function setActivationKey($activationKey)
    {
        $this->activationKey = $activationKey;
    }

    public function run()
    {
        if (empty($this->activationKey)) {
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
            $commands[] = 'bash cldeploy -k '.$this->activationKey;
        }

        $commands = array_merge($commands, $this->installPHPSelector());
        $commands = array_merge($commands, $this->installNodeJSSelector());

        $commands[] = 'mkdir -p /usr/local/omega/web/public/3rdparty';
        $commands[] = 'ln -s /usr/share/l.v.e-manager/commons/spa-resources/ /usr/local/omega/web/public/3rdparty/cloudlinux';

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command.PHP_EOL;
        }

        $commands[] = 'mkdir -p /opt/cpvendor/etc';
        $commands[] = 'cp /usr/local/omega/web/server/cloudlinux/integration.ini /opt/cpvendor/etc/integration.ini';

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

    public function installCageFs()
    {
        $commands = [];
        $commands[] = 'yum install cagefs -y';
        $commands[] = '/usr/sbin/cagefsctl --init';

        return $commands;

    }
    public function installPHPSelector()
    {
        $commands = [];
        $commands[] = 'yum install php php-gd php-mbstring php-pdo php-xml -y';
        $commands[] = 'yum groupinstall alt-php -y';
        $commands[] = 'yum install lvemanager lve-utils -y';
        $commands[] = 'cloudlinux-selector set --json --interpreter php --selector-status enabled';

        return $commands;
    }

    public function installNodeJSSelector()
    {
        $commands = [];
        $commands[] = 'yum install nodejs -y';
        $commands[] = 'yum groupinstall alt-nodejs -y';
        $commands[] = 'yum install lvemanager lve-utils-y';
        $commands[] = 'cloudlinux-selector set --json --interpreter nodejs --selector-status enabled';

        return $commands;
    }

    public static function isCloudLinuxInstalled(): array
    {
        $cloudLinuxVersion = shell_exec('cat /etc/redhat-release');
        if (str_contains($cloudLinuxVersion, 'CloudLinux')) {
            return [
                'status' => 'success',
                'message' => 'CloudLinux is installed.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'CloudLinux is not installed.',
        ];

    }
}
