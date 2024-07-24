<?php

namespace App\Server\Installers\Fail2Ban;

use App\Server\Helpers\OS;

class Fail2BanInstaller
{
    public string $logPath = '/var/log/omega/fail-2-ban-installer.log';

    public $fail2banServers = [];
    public $apacheExtensions = [];

    public function setLogFilePath($path)
    {
        $this->logPath = $path;
    }

    public static function isFail2BanInstalled(): array
    {
        $os = OS::getDistro();

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $command = 'apt list installed | grep fail2ban';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $command = 'yum list installed | grep fail2ban';
        }

        $isInstalled = shell_exec($command);

        if ($isInstalled !== null) {
            return [
                'status' => 'success',
                'message' => 'Fail2Ban is installed.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Fail2Ban is not installed.',
        ];
    }

    public function run()
    {
        $os = OS::getDistro();
        $commands = [];

        if ($os == OS::DEBIAN || $os == OS::UBUNTU) {
            $commands[] = 'apt update -y';
            $commands[] = 'apt install fail2ban -y';
        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            $commands[] = 'yum update -y';
            $commands[] = 'yum install fail2ban -y';
        }

        $commands[] = 'systemctl enable fail2ban';
        $commands[] = 'systemctl start fail2ban';
        $commands[] = 'omega-shell omega:update-fail-2-ban-config';

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }

        $shellFileContent .= 'systemctl is-active --quiet fail2ban ';
        $shellFileContent .= '&& echo "Fail2Ban installed successfully!" ';
        $shellFileContent .= '|| Fail2Ban failed to start!' . PHP_EOL;
        $shellFileContent .= 'echo DONE!' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/fail-2-ban-installer.sh';

        file_put_contents('/tmp/fail-2-ban-installer.sh', $shellFileContent);

        if (!is_dir(dirname($this->logPath))) {
            shell_exec('sudo mkdir -p ' . dirname($this->logPath));
        }

        $this->appendToJailConf();

        shell_exec("bash /tmp/fail-2-ban-installer.sh >> {$this->logPath} 2>&1 &");

        return [
            'status' => 'Install job is running in the background.',
            'message' => 'Fail2Ban is being installed and configured. Please check the log file for more details.',
            'logPath' => $this->logPath,
        ];
    }

    public function setFail2BanServers(array $servers): void
    {
        $this->fail2banServers = $servers;
    }

    public function setApacheExtensions(array $extensions): void
    {
        $this->apacheExtensions = $extensions;
    }

    public function appendToJailConf(): void
    {
        $jailConfPath = resource_path('views/server/samples/fail2ban/fail2ban_jail_conf.blade.php');
        $currentContents = file_exists($jailConfPath) ? file_get_contents($jailConfPath) : '';
        $contentToAppend = view('server.samples.fail2ban.fail2ban_jail_conf')->render();

        if (!empty($this->fail2banServers)) {

            foreach ($this->fail2banServers as $server) {

                $servicesConfigPath = resource_path("views/server/samples/fail2ban/servers/$server/");
                $filePath = $servicesConfigPath . $server . '_conf.blade.php';

                if (file_exists($filePath)) {
                    $serviceContent = file_get_contents($filePath);

                    if (!strpos($currentContents, '[' . $server . ']')) {
                        $contentToAppend .= $serviceContent . "\n";
                    }
                }
            }
        }

        if (!empty($this->apacheExtensions)) {

            foreach ($this->apacheExtensions as $extension) {
                $ext = explode(' - ', $extension);
                $extensionsConfigPath = resource_path("views/server/samples/fail2ban/extensions/$ext[0]/");
                $filePath = $extensionsConfigPath . str_replace(' - ', '_', $extension) . '_conf.blade.php';

                if (file_exists($filePath)) {
                    $serviceContent = file_get_contents($filePath);

                    if (!strpos($currentContents, '[' . $extension . ']')) {
                        $contentToAppend .= $serviceContent . "\n";
                    }
                }
            }
        }
        file_put_contents($jailConfPath, $contentToAppend);
    }
}
