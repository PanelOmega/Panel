<?php

namespace Tests\Unit\Installers\FtpServers;

use Tests\TestCase;
use App\Server\Helpers\OS;
use App\Server\Installers\FtpServers\FtpServerInstaller;

class FtpServerInstallerTest extends TestCase
{

    public function testIsFtpServerInstalled() {

        $supportedDistros = [OS::DEBIAN, OS::UBUNTU, OS::CENTOS, OS::ALMA_LINUX];

        foreach ($supportedDistros as $distro) {

            $os = new OS();
            $current_distro = $os->getDistro();

            if($current_distro !== $distro) {
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
        $supportedDistros = [OS::DEBIAN, OS::UBUNTU, OS::CENTOS, OS::ALMA_LINUX];

        foreach($supportedDistros as $distro) {

            $os = new OS();
            $currentDistro = $os->getDistro();

            if($currentDistro !== $distro) {
                continue;
            }

            $result = (new FtpServerInstaller())->run();

            $expectedCommands = [];
            if ($distro == OS::DEBIAN || $distro == OS::UBUNTU) {
                $expectedCommands[] = 'sudo apt-get update -y';
                $expectedCommands[] = 'sudo apt-get install vsftpd -y';
            } elseif ($distro == OS::CENTOS || $distro == OS::ALMA_LINUX) {
                $expectedCommands[] = 'sudo yum update -y';
                $expectedCommands[] = 'sudo yum install vsftpd -y';
            }
            $expectedCommands[] = 'sudo systemctl restart vsftpd';
            $expectedCommands[] = 'omega-shell omega:update-vsftpd-config';

            $shellFileContent = file_get_contents('/tmp/ftp-server-installer.sh');
            foreach ($expectedCommands as $command) {
                $this->assertStringContainsString($command, $shellFileContent);
            }

            $this->assertEquals('Install job is running in the background.', $result['status']);
            $this->assertEquals('FTP Server is being installed and configured. Please check the log file for more details.', $result['message']);
            $this->assertEquals('/var/log/omega/ftp-server-installer.log', $result['logPath']);

        }
    }
}
