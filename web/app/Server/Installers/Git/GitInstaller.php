<?php

namespace App\Server\Installers\Git;

use App\Server\Helpers\OS;

class GitInstaller
{
    public string $logPath = '/var/log/omega/git-installer.log';

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public function isGitInstalled(): array
    {
        $os = OS::getDistro();

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $command = 'apt list installed | grep git';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $command = 'yum list installed | grep git';
        }

        $isInstalled = shell_exec($command);

        if ($isInstalled !== null) {
            return [
                'status' => 'success',
                'message' => 'Git is installed.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Git is not installed.',
        ];
    }

    public function commands()
    {
        $os = OS::getDistro();
        $commands = [];

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt install git -y';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS
            || $os == OS::ALMA_LINUX
        ) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install git -y';
        }

        if (!is_dir('/root/.config/git')) {
            $commands[] = 'mkdir -p /root/.config/git';
        }

        if (!file_exists('/root/.config/git/ignore')) {
            $commands[] = 'touch /root/.config/git/ignore';
        }

        return $commands;
    }

    public function run()
    {
        $commands = $this->commands();

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'yum list installed | grep git ';
        $shellFileContent .= '&& echo "Git installed successfully!" ';
        $shellFileContent .= '|| Git failed to install!' . PHP_EOL;
        $shellFileContent .= 'echo DONE!' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/git-installer.sh';

        file_put_contents('/tmp/git-installer.sh', $shellFileContent);

        if (!is_dir(dirname($this->logPath))) {
            shell_exec('sudo mkdir -p ' . dirname($this->logPath));
        }

        shell_exec("bash /tmp/git-installer.sh >> {$this->logPath} 2>&1 &");

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'Git is being installed and configured. Please check the log file for more details.',
            'logPath' => $this->logPath,
        ];
    }
}
