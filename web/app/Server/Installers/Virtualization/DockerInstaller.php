<?php

namespace App\Server\Installers\Virtualization;

class DockerInstaller
{
    public $logFilepath = '/var/log/omega/docker-installer.log';
    public function run()
    {

        $commands = [];
        $commands[] = 'sudo apt-get update -y';
        $commands[] = 'sudo apt-get install ca-certificates curl -y';
        $commands[] = 'sudo install -m 0755 -d /etc/apt/keyrings';
        $commands[] = 'sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc';
        $commands[] = 'sudo chmod a+r /etc/apt/keyrings/docker.asc';

        $commands[] = 'echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null';

        $commands[] = 'sudo apt-get update -y';
        $commands[] = 'sudo apt-get install docker-compose docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y';
        $commands[] = 'echo "Done!"';


        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'echo "Docker is installed successfully!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/docker-installer.sh';

        file_put_contents('/tmp/docker-installer.sh', $shellFileContent);

        if (!is_dir(dirname($this->logFilepath))) {
            shell_exec('mkdir -p ' . dirname($this->logFilepath));
        }

        shell_exec('bash /tmp/docker-installer.sh >> ' . $this->logFilepath . ' &');

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'Docker is being installed in the background. Please check the log file for more details.',
            'logFilepath' => $this->logFilepath
        ];
    }
}
