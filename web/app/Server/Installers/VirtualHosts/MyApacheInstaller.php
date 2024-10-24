<?php

namespace App\Server\Installers\VirtualHosts;

use App\Server\Helpers\OS;

class MyApacheInstaller
{

    public string $logPath = '/var/log/omega/my-apache-installer.log';


    public static function isApacheInstalled(): array
    {
        $os = OS::getDistro();

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $dockerVersion = shell_exec('service apache2 status');
        } elseif ($os == OS::CLOUD_LINUX ||$os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $dockerVersion = shell_exec('systemctl status httpd');
        }

        if (str_contains($dockerVersion, 'The Apache HTTP Server')) {
            return [
                'status' => 'success',
                'message' => 'Apache2 is installed.',
                'version' => $dockerVersion,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Apache2 is not installed.',
        ];
    }

    public function commands()
    {
        $os = OS::getDistro();

        $commands = [];
        $commands[] = 'echo "Starting MyApache Installation..."';

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {

            $commands[] = 'export DEBIAN_FRONTEND=noninteractive';
            $commands[] = 'apt-get install -yq sudo';
            $commands[] = 'add-apt-repository -y ppa:ondrej/apache2';
            $commands[] = 'apt-get install -yq apache2';
            $commands[] = 'apt-get install -yq apache2-suexec-custom';
            $commands[] = 'apt-get install -yq libapache2-mod-ruid2';

            $commands[] = 'a2enmod cgi';
            $commands[] = 'a2enmod mime';
            $commands[] = 'a2enmod rewrite';
            $commands[] = 'a2enmod env';
            $commands[] = 'a2enmod ssl';
            $commands[] = 'a2enmod actions';
            $commands[] = 'a2enmod headers';
            $commands[] = 'a2enmod suexec';
            $commands[] = 'a2enmod ruid2';
            $commands[] = 'a2enmod proxy';
            $commands[] = 'a2enmod proxy_http';

            // For Fast CGI
            $commands[] = 'a2enmod fcgid';
            $commands[] = 'a2enmod alias';
            $commands[] = 'a2enmod proxy_fcgi';
//        $commands[] = 'a2enmod setenvif';

            // $commands[] = 'ufw allow in "Apache Full"';
            $commands[] = 'systemctl restart apache2';

        } else {
            if ($os == OS::CLOUD_LINUX || $os == OS::ALMA_LINUX) {
                $commands[] = 'yum install -y epel-release';
                $commands[] = 'yum install -y my-apache';

                $commands[] = 'systemctl enable httpd';
                $commands[] = 'systemctl start httpd';

            }
        }
        return $commands;
    }

    public function run() {

        $commands = $this->commands();

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }
        $shellFileContent .= 'echo "MyApache is installed successfully!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/my-apache-installer.sh';

        file_put_contents('/tmp/my-apache-installer.sh', $shellFileContent);

        if (! is_dir(dirname($this->logPath))) {
            shell_exec('mkdir -p '.dirname($this->logPath));
        }

        shell_exec('bash /tmp/my-apache-installer.sh >> ' . $this->logPath . ' &');

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'MyApache is being installed in the background. Please check the log file for more details.',
            'logPath' => $this->logPath,
        ];
    }
}
