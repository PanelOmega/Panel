<?php

namespace tests\Unit\Models;

use App\Models\FirewallRule;
use App\Server\Helpers\OS;
use Illuminate\Foundation\Testing\TestCase;

class FirewallRuleTest extends TestCase
{
    public function testAllowPortAction() {
        $testAction = 'allow';
        $testPort = '25';
        $testComment = 'testComment';

        shell_exec('systemctl enable ufw');
        shell_exec('systemctl restart ufw');
        shell_exec('echo "y" | ufw enable');
        $command = 'ufw status | grep active';
        $testEnabledUfw = shell_exec($command);
        $this->assertEquals('Status: active', trim($testEnabledUfw));

        $command = "ufw {$testAction} {$testPort} comment {$testComment}";
        $testAllowPort = shell_exec($command);
        $testOutput = 'Rule updated';

        $this->assertTrue(str_contains($testAllowPort, $testOutput));

        $command = "ufw status";
        $testAllowedOutput = shell_exec($command);
        $this->assertNotEmpty($testAllowedOutput);
        $pattern = "/{$testPort}\s+ALLOW\s+Anywhere\s+#\s*{$testComment}/";
        $this->assertTrue(preg_match($pattern, $testAllowedOutput) === 1);

        $testFirewallRulesArr = shell_exec('ufw status numbered | jc --ufw');
        $testFirewallRulesArr = json_decode($testFirewallRulesArr, true);
        $this->assertNotEmpty($testFirewallRulesArr);
        $this->assertArrayHasKey('status', $testFirewallRulesArr);
        $this->assertArrayHasKey('rules', $testFirewallRulesArr);
        $this->assertEquals('active', $testFirewallRulesArr['status']);
    }

    public function testDenyPortAction() {
        $testAction = 'deny';
        $testPort = '25';
        $testComment = 'testComment';

        shell_exec('systemctl enable ufw');
        shell_exec('systemctl restart ufw');
        shell_exec('echo "y" | ufw enable');
        $command = 'ufw status | grep active';
        $testEnabledUfw = shell_exec($command);
        $this->assertEquals('Status: active', trim($testEnabledUfw));

        $command = "ufw allow {$testPort}";
        shell_exec($command);

        $command = "ufw status | grep {$testPort}";
        $testAllowedOutput = shell_exec($command);
        $this->assertNotEmpty($testAllowedOutput);

        $command = "ufw {$testAction} {$testPort} comment {$testComment}";
        $testDenyPort = shell_exec($command);
        $testOutput = 'Rule updated';

        $this->assertTrue(str_contains($testDenyPort, $testOutput));

        $command = "ufw status | grep {$testPort}";
        $testDeniedOutput = shell_exec($command);
        $this->assertNotEmpty($testDeniedOutput);

        $pattern = "/{$testPort}\s+DENY\s+Anywhere\s+#\s*{$testComment}/";
        $this->assertTrue(preg_match($pattern, $testDeniedOutput) === 1);

        $testFirewallRulesArr = shell_exec('ufw status numbered | jc --ufw');
        $testFirewallRulesArr = json_decode($testFirewallRulesArr, true);
        $this->assertNotEmpty($testFirewallRulesArr);
        $this->assertArrayHasKey('status', $testFirewallRulesArr);
        $this->assertArrayHasKey('rules', $testFirewallRulesArr);
        $this->assertEquals('active', $testFirewallRulesArr['status']);
    }

    public function testIsEnabled() {
        shell_exec('echo "y" | ufw enable');
        $command = 'ufw status';
        $testEnabledUfw = shell_exec($command);
        $this->assertTrue(str_contains($testEnabledUfw, 'Status: active'));
    }

    public function testEnableFirewallWithUfwInstalled() {
        $testOutput = 'Firewall is active';
        $command = 'ufw --force enable';
        $testEnabledUfw = shell_exec($command);
        $this->assertTrue(str_contains($testEnabledUfw, $testOutput));
        $testEnableSystemPorts = FirewallRule::enableSystemPorts();
        $this->assertNull($testEnableSystemPorts);

        $testFirewallRulesArr = shell_exec('ufw status numbered | jc --ufw');
        $testFirewallRulesArr = json_decode($testFirewallRulesArr, true);
        $this->assertNotEmpty($testFirewallRulesArr);
        $this->assertArrayHasKey('status', $testFirewallRulesArr);
        $this->assertArrayHasKey('rules', $testFirewallRulesArr);
        $this->assertEquals('active', $testFirewallRulesArr['status']);
    }

    public function testEnableFirewallWithoutUfwInstalled() {
        $os = OS::getDistro();
        $this->assertNotEmpty($os);
        $testOutput = 'Firewall is active';
        shell_exec('dnf install ufw jc -y');
        $command = 'ufw --force enable';
        $testEnabledUfw = shell_exec($command);
        $this->assertTrue(str_contains($testEnabledUfw, $testOutput));
        $testEnableSystemPorts = FirewallRule::enableSystemPorts();
        $this->assertNull($testEnableSystemPorts);

        $testFirewallRulesArr = shell_exec('ufw status numbered | jc --ufw');
        $testFirewallRulesArr = json_decode($testFirewallRulesArr, true);
        $this->assertNotEmpty($testFirewallRulesArr);
        $this->assertArrayHasKey('status', $testFirewallRulesArr);
        $this->assertArrayHasKey('rules', $testFirewallRulesArr);
        $this->assertEquals('active', $testFirewallRulesArr['status']);
    }

    public function testEnableSystemPorts() {
        $testAction = 'allow';
        $testPort = '20';
        $testComment = 'testPanelOmega - FTP';
        $reflection = new \ReflectionClass(FirewallRule::class);
        $method = $reflection->getMethod('_portAction');
        $method->setAccessible(true);
        $method->invoke(null, $testAction, $testPort, $testComment);
        $command = 'ufw status';
        $testEnabledPortOutput = shell_exec($command);
        $this->assertNotEmpty($testEnabledPortOutput);
        $pattern = "/{$testPort}\s+ALLOW\s+Anywhere\s+#\s*{$testComment}/";
        $this->assertTrue(preg_match($pattern, $testEnabledPortOutput) === 1);

        $testFirewallRulesArr = shell_exec('ufw status numbered | jc --ufw');
        $testFirewallRulesArr = json_decode($testFirewallRulesArr, true);
        $this->assertNotEmpty($testFirewallRulesArr);
        $this->assertArrayHasKey('status', $testFirewallRulesArr);
        $this->assertArrayHasKey('rules', $testFirewallRulesArr);
        $this->assertEquals('active', $testFirewallRulesArr['status']);
    }
}
