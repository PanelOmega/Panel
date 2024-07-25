<?php

namespace App\Server\Installers\Fail2Ban;

use App\Server\Helpers\OS;

class Fail2BanInstaller
{
    public string $logPath = '/var/log/omega/fail-2-ban-installer.log';

    public $fail2banServers = [];
    public $apacheExtensions = [];

    public $nginxExtensions = [];

    public $wpExtensions = [];

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

    public function setNginxExtensions(array $extensions): void
    {
        $this->nginxExtensions = $extensions;
    }

    public function setWordpressExtensions(array $extensions): void
    {
        $this->wpExtensions = $extensions;
    }

    public function appendToJailConf(): void
    {
        $jailConfPath = resource_path('views/server/samples/fail2ban/fail2ban_jail_conf.blade.php');
        $currentContents = file_exists($jailConfPath) ? file_get_contents($jailConfPath) : '';

        $filterDPath = '/etc/fail2ban/filter.d';
        $templatesFilterDirPath = resource_path('views/server/samples/fail2ban/filters');

        $extensionsArr = [
            $this->apacheExtensions,
            $this->nginxExtensions,
            $this->wpExtensions,
        ];

        $this->handleServers($this->fail2banServers, $currentContents, $filterDPath, $templatesFilterDirPath);
        $this->handleExtensions($extensionsArr, $currentContents, $filterDPath, $templatesFilterDirPath);

        file_put_contents($jailConfPath, $currentContents);
    }

    public function handleServers(array $fail2banServers, string &$currentContents, string $filterDPath, string $templatesFilterDirPath): void
    {
        if (!empty($fail2banServers)) {
            foreach ($fail2banServers as $server) {
                $templateFile = $templatesFilterDirPath . "/$server/$server.blade.php";
                $filterDFile = $filterDPath . "/$server.conf";

                if (file_exists($templateFile) && !file_exists($filterDFile)) {
                    $filterContents = file_get_contents($templateFile);
                    file_put_contents($filterDFile, $filterContents);
                }

                $serversConfigPath = resource_path("views/server/samples/fail2ban/servers/$server/");
                $filePath = $serversConfigPath . $server . '_conf.blade.php';

                if (file_exists($filePath)) {
                    $serverContent = file_get_contents($filePath);

                    if (!strpos($currentContents, '[' . $server . ']')) {
                        $currentContents .= $serverContent . "\n";
                    }
                }
            }
        }
    }

    public function handleExtensions(array $extensionsArr, string &$currentContents, string $filterDPath, string $templatesFilterDirPath): void
    {
        foreach ($extensionsArr as $extension_element) {
            if (!empty($extension_element)) {
                foreach ($extension_element as $extension) {
                    $ext = explode(' - ', $extension);
                    $extFilter = str_replace(' - ', '_', $extension);
                    $extFilterD = str_replace(' - ', '-', $extension);

                    $templateFile = $templatesFilterDirPath . "/$ext[0]/$extFilter.blade.php";
                    $filterDFile = $filterDPath . "/$extFilterD.conf";

                    if (file_exists($templateFile) && !file_exists($filterDFile)) {
                        $filterContents = file_get_contents($templateFile);
                        file_put_contents($filterDFile, $filterContents);
                    }

                    ($ext[0] == 'php' && $ext[1] == 'url') ? $ext[0] = 'apache' : '';

                    $extensionsConfigPath = resource_path("views/server/samples/fail2ban/extensions/$ext[0]/");
                    $filePath = $extensionsConfigPath . str_replace(' - ', '_', $extension) . '_conf.blade.php';

                    if (file_exists($filePath)) {
                        $serviceContent = file_get_contents($filePath);
                        if (!strpos($currentContents, '[' . $extension . ']')) {
                            $currentContents .= $serviceContent . "\n";
                        }
                    }
                }
            }
        }
    }
}
