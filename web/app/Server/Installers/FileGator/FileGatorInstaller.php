<?php

namespace App\Server\Installers\FileGator;

use App\Server\Helpers\OS;

class FileGatorInstaller
{

    public string $logPath = '/var/log/omega/filegator-nstaller.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public function run()
    {
        $os = OS::getDistro();
        $commands = [];

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'sudo apt-get update -y';

        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $commands[] = 'sudo yum update -y';
        }

        $commands[] = 'wget https://github.com/filegator/static/raw/master/builds/filegator_latest.zip';
        $commands[] = 'unzip filegator_latest.zip && rm filegator_latest.zip';
        $commands[] = 'chmod -R 775 filegator/';

        $mvDir = '/usr/local/omega/web/thirdparty/filegator/dist';
        $isDirExist = shell_exec($mvDir);

        if (!str_contains($isDirExist, '/usr/local/omega/web/thirdparty/filegator/dist')) {
            shell_exec('sudo mkdir -p ' . $mvDir);
        }

        $commands[] = 'sudo mv filegator ' . $mvDir;

        $commands[] = 'a2dissite 000-default.conf';
        $commands[] = 'a2ensite filegator.conf';
        $commands[] = 'systemctl restart apache2';

        foreach ($commands as $command) {
            shell_exec($command);
        }


    }
}
