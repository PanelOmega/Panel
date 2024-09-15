<?php

namespace App\Server\Installers\DatabaseManagers;

use App\Server\Helpers\OS;

class PHPMyAdminInstaller
{

    public string $logPath = '/var/log/omega/phpmyadmin-server-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public static function isPHPMyAdminInstalled(): array
    {
        $os = OS::getDistro();

        return [
            'status' => 'error',
            'message' => 'PHPMyAdmin is not installed.',
        ];
    }

    public function run()
    {

        // https://wiki.crowncloud.net/?How_to_Install_phpMyAdmin_on_AlmaLinux_9

        $os = OS::getDistro();
        $commands = [];

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt-get update';
            $commands[] = 'apt-get install phpmyadmin -y';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $commands[] = 'dnf install phpmyadmin -y';
        }

         $commands[] = 'yes | cp /usr/local/omega/web/app/Server/Installers/DatabaseManagers/SSO/omega-sso.php.dist /usr/share/phpMyAdmin/omega-sso.php';
         $commands[] = 'mkdir -p /usr/local/omega/data/sessions';

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }


        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/phpmyadmin-server-installer.sh';

        file_put_contents('/tmp/phpmyadmin-server-installer.sh', $shellFileContent);

        if (!is_dir(dirname($this->logPath))) {
            shell_exec('mkdir -p ' . dirname($this->logPath));
        }

        shell_exec('bash /tmp/phpmyadmin-server-installer.sh >> ' . $this->logPath . ' 2>&1 &');

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'PHPMyAdmin installation job is running in the background. You can check the log file for more details',
            'logPath' => $this->logPath,
        ];
    }
}
