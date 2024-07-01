<?php

    namespace App\Server\Installers\FtpServers;


    class FtpServerInstaller {

        public string $logPath = '/var/log/omega/ftp-server-installer.log';

        public static function isFtpServerInstalled(): array
        {
            $vsftpdVersion = shell_exec('vsftpd -v');
            if (str_contains($vsftpdVersion, 'vsftpd')) {
                return [
                    'status' => 'success',
                    'message' => 'FTP Server (vsftpd) is installed.',
                    'version' => $vsftpdVersion,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'FTP Server (vsftpd) is not installed.',
            ];
        }

        public function run()
        {
            $commands = [];
            $commands[] = 'sudo apt-get update -y';
            $commands[] = 'sudo apt-get install vsftpd -y';

            $commands[] = 'sudo sed -i "s/#write_enable=YES/write_enable=YES/" /etc/vsftpd.conf';
            $commands[] = 'sudo systemctl restart vsftpd';

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