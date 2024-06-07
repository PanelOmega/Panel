<?php

namespace Tests\Unit\Traits;

use App\Server\Installers\VirtualHosts\ApacheInstaller;
use App\Server\Installers\Virtualization\DockerInstaller;

trait HasDocker
{
    public function installDocker()
    {

        $checkApache2IsInstalled = ApacheInstaller::isApacheInstalled();
        if ($checkApache2IsInstalled['status'] === 'error') {

            // Install apache2
            $install = new ApacheInstaller();
            $installStatus = $install->run();

            $this->assertArrayHasKey('status', $installStatus);
            $this->assertSame('Install job is running in the background.', $installStatus['status']);

            $isApache2Installed = false;
            for ($i = 0; $i < 200; $i++) {
                if (is_file($installStatus['logPath'])) {
                    $apache2InstallLog = file_get_contents($installStatus['logPath']);
                    if (str_contains($apache2InstallLog, 'Apache2 is installed successfully!')) {
                        $isApache2Installed = true;
                        break;
                    }
                }
                sleep(1);
            }

            $this->assertTrue($isApache2Installed);
        }

        $checkDockerIsInstalled = DockerInstaller::isDockerInstalled();
        if ($checkDockerIsInstalled['status'] === 'error') {

            // Install docker
            $install = new DockerInstaller();
            $installStatus = $install->run();

            $this->assertArrayHasKey('status', $installStatus);
            $this->assertSame('Install job is running in the background.', $installStatus['status']);

            $isDockerInstalled = false;
            for ($i = 0; $i < 200; $i++) {
                if (is_file($installStatus['logPath'])) {
                    $dockerInstallLog = file_get_contents($installStatus['logPath']);
                    if (str_contains($dockerInstallLog, 'Docker is installed successfully!')) {
                        $isDockerInstalled = true;
                        break;
                    }
                }
                sleep(1);
            }

            $this->assertTrue($isDockerInstalled);

        }
    }
}
