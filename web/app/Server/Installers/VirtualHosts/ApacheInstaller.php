<?php

namespace App\Server\Installers\VirtualHosts;

class ApacheInstaller
{

    public string $logPath = '/var/log/omega/apache-installer.log';


    public static function isApacheInstalled(): array
    {
        $dockerVersion = shell_exec('apache2 -v');
        if (str_contains($dockerVersion, 'Apache2 version')) {
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

    public function run()
    {
        $commands = [];
        $commands[] = 'echo "Starting Apache Installation..."';
        $commands[] = 'export DEBIAN_FRONTEND=noninteractive';
        $commands[] = 'apt-get install -yq sudo';
        $commands[] = 'add-apt-repository -y ppa:ondrej/apache2';

        $dependenciesList = [
            'apache2',
            'apache2-suexec-custom',
            'libapache2-mod-ruid2'
        ];


        $dependencies = implode(' ', $dependenciesList);
        $commands[] = 'apt-get install -yq ' . $dependencies;

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

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }
        $shellFileContent .= 'echo "All packages installed successfully!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/apache-installer.sh';

        file_put_contents('/tmp/apache-installer.sh', $shellFileContent);
        shell_exec('bash /tmp/apache-installer.sh >> ' . $this->logPath . ' &');

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'Apache2 is being installed in the background. Please check the log file for more details.',
            'logPath' => $this->logPath,
        ];
    }
}
