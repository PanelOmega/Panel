<?php

namespace tests\Unit\Models;

use App\Jobs\Fail2BanConfigBuild;
use App\Services\Fail2Ban\Fail2BanBannedIp\Fail2BanBannedIpService;
use Illuminate\Foundation\Testing\TestCase;

class Fail2BanBannedIpTest extends TestCase
{
    public function testBanIp() {
        $testIp = '123.123.123.123';
        $testService = 'vsftpd';

        $command = "fail2ban-client status | grep {$testService}";
        $testCheckService = shell_exec($command);

        if(empty($testCheckService)) {
            $testFail2BanConfig = new Fail2BanConfigBuild();
            $testPathToJailConfig = '/etc/fail2ban/jail.local';
            $settings['jails'][$testService]['enabled'] = true;
            $testContent = $testFail2BanConfig->getJailLocalConf([], $settings);
            file_put_contents($testPathToJailConfig, $testContent);
            shell_exec('systemctl restart fail2ban');
            $testFail2BanConfig->firewalldBuild();
        }

        $testBanIp = Fail2BanBannedIpService::banIp($testIp, $testService);
        $this->assertTrue($testBanIp);

        $testBannedIps = Fail2BanBannedIpService::getBannedIp();
        $this->assertNotEmpty($testBannedIps);
        $this->assertTrue(in_array($testIp, $testBannedIps));
        $this->assertTrue(in_array('BANNED', $testBannedIps));
        $this->assertTrue(in_array($testService, $testBannedIps));

        $testUnbanIp = Fail2BanBannedIpService::unBanIp($testIp, $testService);
        $this->assertTrue($testUnbanIp);
    }

    public function testUnbanIp() {
        $testIp = '123.123.123.123';
        $testService = 'vsftpd';

        $command = "fail2ban-client status | grep {$testService}";
        $testCheckService = shell_exec($command);

        if(empty($testCheckService)) {
            $testFail2BanConfig = new Fail2BanConfigBuild();
            $testPathToJailConfig = '/etc/fail2ban/jail.local';
            $settings['jails']['vsftpd']['enabled'] = true;
            $testContent = $testFail2BanConfig->getJailLocalConf([], $settings);
            file_put_contents($testPathToJailConfig, $testContent);
            shell_exec('systemctl restart fail2ban');
            $testFail2BanConfig->firewalldBuild();
        }

        $testBanIp = Fail2BanBannedIpService::banIp($testIp, $testService);
        $this->assertTrue($testBanIp);

        $testUnbanIp = Fail2BanBannedIpService::unbanIp($testIp, $testService);
        $this->assertTrue($testUnbanIp);

        $testBannedIps = Fail2BanBannedIpService::getBannedIp();
        $this->assertEmpty($testBannedIps);
        $this->assertFalse(in_array($testIp, $testBannedIps));
        $this->assertFalse(in_array('BANNED', $testBannedIps));
        $this->assertFalse(in_array($testService, $testBannedIps));
    }
}
