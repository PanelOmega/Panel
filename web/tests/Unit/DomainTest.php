<?php

namespace Tests\Unit;

use App\Models\Domain;
use App\Server\Installers\Virtualization\DockerInstaller;
use App\Virtualization\Docker\DockerApi;
use Tests\TestCase;

class DomainTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testDomainCreation(): void
    {
        $checkDockerIsInstalled = DockerApi::isDockerInstalled();
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

        $domainDetails = [
            'domain' => 'example.com',
            'status' => 'active',
        ];

        $domain = new Domain();
        $domain->fill($domainDetails);
        $domain->save();

        $this->assertDatabaseHas('domains', $domainDetails);


    }
}
