<?php

namespace Tests\Unit\Traits;

use App\Server\Installers\Virtualization\DockerInstaller;
use App\Virtualization\Docker\DockerApi;

trait HasDocker
{
    public function installDocker()
    {
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
