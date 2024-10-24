<?php

namespace App\Server\Installers\Web;

use App\Server\Helpers\OS;

class PHPInstaller
{
    public $phpVersions = [
        '5.6',
        '7.4',
        '8.0',
        '8.1',
        '8.2',
        '8.3',
    ];
    public $phpModules = [];
    public $logFilePath = '/var/log/omega/php-installer.log';

    public function setPHPVersions($versions)
    {
        $this->phpVersions = $versions;
    }

    public function setPHPModules($modules)
    {
        $this->phpModules = $modules;
    }

    public function setLogFilePath($path)
    {
        $this->logFilePath = $path;
    }

    public function commands()
    {
        $os = OS::getDistro();

        $commands = [];
        $commands[] = 'echo "Starting PHP Installation..."';


        // mod_suphp

        if ($os == OS::UBUNTU) {
            $commands[] = 'export DEBIAN_FRONTEND=noninteractive';
            $commands[] = 'apt-get install -yq sudo';
            $commands[] = 'add-apt-repository -y ppa:ondrej/php';
            $commands[] = 'add-apt-repository -y ppa:ondrej/apache2';

            $dependenciesList = [
                'apache2',
                'apache2-suexec-custom',
                'libapache2-mod-ruid2'
            ];
            if (!empty($this->phpVersions)) {
                foreach ($this->phpVersions as $phpVersion) {
                    $dependenciesList[] = 'libapache2-mod-php'.$phpVersion;
                }
                if (!empty($this->phpModules)) {
                    foreach ($this->phpVersions as $phpVersion) {
                        $dependenciesList[] = 'php'.$phpVersion;
                        $dependenciesList[] = 'php'.$phpVersion.'-cgi';
                        $dependenciesList[] = 'php'.$phpVersion.'-{'
                            .implode(',', $this->phpModules).'}';
                    }
                }
            }


            $dependencies = implode(' ', $dependenciesList);
            $commands[] = 'apt-get install -yq '.$dependencies;

            $lastItem = end($this->phpVersions);
            foreach ($this->phpVersions as $phpVersion) {
                if ($phpVersion == $lastItem) {
                    $commands[] = 'a2enmod php'.$phpVersion;
                } else {
                    $commands[] = 'a2dismod php'.$phpVersion;
                }
            }

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
//        $commands[] = 'a2enmod fcgid';
//        $commands[] = 'a2enmod alias';
//        $commands[] = 'a2enmod proxy_fcgi';
//        $commands[] = 'a2enmod setenvif';

            // $commands[] = 'ufw allow in "Apache Full"';
            $commands[] = 'systemctl restart apache2';

        }
        if ($os == OS::ALMA_LINUX || $os == OS::CLOUD_LINUX) {

            $commands[] = 'dnf update -y';
            $commands[] = 'dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm -y';
            $commands[] = 'dnf install https://rpms.remirepo.net/enterprise/remi-release-9.rpm -y';
            $commands[] = 'dnf install php -y';

            foreach ($this->phpVersions as $phpVersion) {
                $phpVersionWithoutDot = str_replace('.', '', $phpVersion);
                $commands[] = 'dnf module enable php:remi-'.$phpVersion.' -y';
                $commands[] = 'dnf install php'.$phpVersionWithoutDot.' php' .$phpVersionWithoutDot.'-php-fpm -y';
            }
        }

        $commands[] = 'omega-shell cache:clear';

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
        $shellFileContent .= 'rm -f /tmp/php-installer.sh';

        //shell_exec('sudo chmod 755 /tmp');
        file_put_contents('/tmp/php-installer.sh', $shellFileContent);
        shell_exec('bash /tmp/php-installer.sh >> ' . $this->logFilePath . ' &');


        return [
            'status' => 'Install job is running in the background.',
            'message' => 'PHP versions is being installed in the background. Please check the log file for more details.',
            'logPath' => $this->logFilePath,
        ];
    }

    public function installIonCube()
    {

        // 64  bit
        // $ wget https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz
        // tar -zxvf ioncube_loaders_lin_x86*
        //  cd ioncube/
        // php -i | grep extension_dir
        // sudo cp /tmp/ioncube/ioncube_loader_lin_7.4.so /usr/lib/php/20190902

//         sudo vi /etc/php/8.2/cli/php.ini 		#for PHP CLI
//         sudo vi /etc/php/8.2/fpm/php.ini		#for PHP-FPM & Nginx
//         sudo vi /etc/php/8.2/apache2/php.ini	        #for Apache2

        // zend_extension = /usr/lib/php/20190902/ioncube_loader_lin_8.2.so

        // command to add zend_extension to the php.ini file -cphp8.2-cgi.ini
        // sudo echo "zend_extension = /usr/lib/php/20190902/ioncube_loader_lin_8.2.so" | sudo tee -a /etc/php/8.2/cgi/php.ini
        // sudo echo "zend_extension = /usr/lib/php/20190902/ioncube_loader_lin_8.2.so" | sudo tee -a /etc/php/8.2/apache2/php.ini
        // sudo echo "zend_extension = /usr/lib/php/20190902/ioncube_loader_lin_8.2.so" | sudo tee -a /etc/php/8.2/cli/php.ini
    }
}
