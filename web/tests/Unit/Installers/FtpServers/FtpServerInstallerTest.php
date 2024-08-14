<?php

namespace Tests\Unit\Installers\FtpServers;

use App\Server\Helpers\OS;
use App\Server\Installers\FtpServers\FtpServerInstaller;
use Tests\TestCase;

class FtpServerInstallerTest extends TestCase
{

    public function testIsFtpServerInstalled()
    {

        $supportedDistros = [OS::CLOUD_LINUX, OS::DEBIAN, OS::UBUNTU, OS::CENTOS, OS::ALMA_LINUX];

        foreach ($supportedDistros as $distro) {

            $os = new OS();
            $currentDistro = $os->getDistro();

            if ($currentDistro !== $distro) {
                continue;
            }

            $result = FtpServerInstaller::isFtpServerInstalled();


            if (!empty(shell_exec('rpm -qa | grep vsftpd')) || !empty(shell_exec('apt-cache policy vsftpd | grep Installed'))) {
                $this->assertEquals('success', $result['status']);
                $this->assertEquals('FTP Server (vsftpd) is installed.', $result['message']);
            } else {
                $this->assertEquals('error', $result['status']);
                $this->assertEquals('FTP Server (vsftpd) is not installed.', $result['message']);
            }
        }
    }

    public function testRun()
    {
        $supportedDistros = [OS::CLOUD_LINUX, OS::DEBIAN, OS::UBUNTU, OS::CENTOS, OS::ALMA_LINUX];

        foreach ($supportedDistros as $distro) {

            $os = new OS();
            $currentDistro = $os->getDistro();

            if ($currentDistro !== $distro) {
                continue;
            }

            $result = (new FtpServerInstaller())->run();

            $this->assertEquals('Install job is running in the background.', $result['status']);
            $this->assertEquals('FTP Server is being installed and configured. Please check the log file for more details.', $result['message']);
            $this->assertEquals('/var/log/omega/ftp-server-installer.log', $result['logPath']);

            $isFtpServerInstalled = false;
            $ftpServerFailedToStart = false;
            for ($i = 0; $i < 200; $i++) {
                if (is_file($result['logPath'])) {
                    $ftpServerLogPath = file_get_contents($result['logPath']);
                    if (str_contains($ftpServerLogPath, 'FTP Server is installed and configured successfully!')) {
                        $isFtpServerInstalled = true;
                        break;
                    } elseif (str_contains($ftpServerLogPath, 'FTP Server failed to start')) {
                        $ftpServerFailedToStart = true;
                        break;
                    }
                }
                sleep(1);
            }

            $this->assertTrue($isFtpServerInstalled);
            $this->assertFalse($ftpServerFailedToStart);

        }
    }
}
