<?php

namespace Tests\Unit\Traits;

use App\Server\Helpers\PHP;
use App\Server\Installers\VirtualHosts\ApacheInstaller;
use App\Server\Installers\Web\PHPInstaller;
use Illuminate\Support\Facades\Artisan;

trait HasPHP
{

    public function installPHP()
    {
        $requestedPHPVersions = [
            '8.0',
            '8.1',
            '8.2'
        ];

        $getPHPVersions = PHP::getInstalledPHPVersions();
        if (empty($getPHPVersions)) {

            // Install PHP
            $install = new PHPInstaller();
            $install->setPHPVersions($requestedPHPVersions);
            $installStatus = $install->run();

            $this->assertArrayHasKey('status', $installStatus);
            $this->assertSame('Install job is running in the background.', $installStatus['status']);

            $isPHPInstalled = false;
            for ($i = 0; $i < 200; $i++) {
                if (is_file($installStatus['logPath'])) {
                    $apache2InstallLog = file_get_contents($installStatus['logPath']);
                    if (str_contains($apache2InstallLog, 'All packages installed successfully!')) {
                        $isPHPInstalled = true;
                        break;
                    }
                }
                sleep(1);
            }

            $this->assertTrue($isPHPInstalled);

        }

        $getPHPVersions = PHP::getInstalledPHPVersions();
        $this->assertNotEmpty($getPHPVersions);

        foreach ($getPHPVersions as $phpVersion) {
            $this->assertTrue(in_array($phpVersion['short'], $requestedPHPVersions));
        }

    }
}
