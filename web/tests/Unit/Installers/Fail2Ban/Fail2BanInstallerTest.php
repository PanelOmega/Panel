<?php

namespace Installers\Fail2Ban;

use App\Console\Commands\UpdateFail2BanConfig;
use App\Server\Installers\Fail2Ban\Fail2BanInstaller;
use Tests\TestCase;

class Fail2BanInstallerTest extends TestCase
{

    public function testIsFail2BanInstalled()
    {

        $update = new UpdateFail2BanConfig();
        $update->handle();


        $result = Fail2BanInstaller::isFail2BanInstalled();

        if (!empty(shell_exec('rpm -qa | grep fail2ban'))) {
            $this->assertEquals('success', $result['status']);
            $this->assertEquals('Fail2Ban is installed.', $result['message']);
        } else {
            $this->assertEquals('error', $result['status']);
            $this->assertEquals('Fail2Ban is not installed.', $result['message']);
        }
    }

    public function testRun()
    {
        $result = (new Fail2BanInstaller())->run();
        $this->assertEquals('Install job is running in the background.', $result['status']);
        $this->assertEquals('Fail2Ban is being installed and configured. Please check the log file for more details.', $result['message']);
        $this->assertEquals('/var/log/omega/fail-2-ban-installer.log', $result['logPath']);

        $isFail2BanInstalled = false;
        $fail2banFailedToStart = false;

        for ($i = 0; $i < 200; $i++) {
            if (is_file($result['logPath'])) {
                $fail2banLogPath = file_get_contents($result['logPath']);

                if (str_contains($fail2banLogPath, 'Fail2Ban installed successfully!')) {
                    $isFail2BanInstalled = true;
                    break;
                } elseif (str_contains($fail2banLogPath, 'Fail2Ban failed to start')) {
                    $fail2banFailedToStart = true;
                    break;
                }
            }
            sleep(1);
        }

        $this->assertTrue($isFail2BanInstalled);
        $this->assertFalse($fail2banFailedToStart);
    }

}
