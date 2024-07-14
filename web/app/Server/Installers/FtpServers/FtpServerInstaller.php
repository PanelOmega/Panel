<?php

    namespace App\Server\Installers\FtpServers;

    use App\Server\Helpers\OS;

    class FtpServerInstaller {

        public string $logPath = '/var/log/omega/ftp-server-installer.log';

        public function setLogFilePath($path)
        {
            $this->logPath = $path;
        }

        public static function isFtpServerInstalled(): array
        {
            $os = OS::getDistro();

            if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
                $isInstalled = shell_exec('apt-cache policy vsftpd | grep Installed');
            } elseif($os == OS::CENTOS || $os == OS::ALMA_LINUX) {
                $isInstalled = shell_exec('rpm -qa | grep vsftpd');
            }

            if (str_contains($isInstalled, 'Installed') || str_contains($isInstalled, 'vsftpd')) {
                return [
                    'status' => 'success',
                    'message' => 'FTP Server (vsftpd) is installed.',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'FTP Server (vsftpd) is not installed.',
            ];
        }

        public function run()
        {
            $os = OS::getDistro();
            $commands = [];


            if($os == OS::DEBIAN || $os == OS::UBUNTU) {
                $commands[] = 'sudo apt-get update -y';
                $commands[] = 'sudo apt-get install vsftpd -y';
            } elseif($os == OS::CENTOS || $os == OS::ALMA_LINUX) {
                $commands[] = 'sudo yum update -y';
                $commands[] = 'sudo yum install vsftpd -y';
            }

            $commands[] = 'sudo systemctl restart vsftpd';
            $commands[] = 'omega-shell omega:update-vsftpd-config';

            $shellFileContent = '';
            foreach ($commands as $command) {
                $shellFileContent .= $command . PHP_EOL;
            }

            $shellFileContent .= 'echo "FTP Server is installed and configured successfully!"' . PHP_EOL;
            $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
            $shellFileContent .= 'rm -f /tmp/ftp-server-installer.sh';

            file_put_contents('/tmp/ftp-server-installer.sh', $shellFileContent);

            if (!is_dir(dirname($this->logPath))) {
                shell_exec('mkdir -p ' . dirname($this->logPath));
            }

            shell_exec('bash /tmp/ftp-server-installer.sh >> ' . $this->logPath . ' 2>&1 &');

            return [
                'status' => 'Install job is running in the background.',
                'message' => 'FTP Server is being installed and configured. Please check the log file for more details.',
                'logPath' => $this->logPath,
            ];
        }
    }
