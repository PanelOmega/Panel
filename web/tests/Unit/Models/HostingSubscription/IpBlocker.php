<?php

namespace Models\HostingSubscription;

use App\Models\Customer;
use PHPUnit\Framework\TestCase;

class IpBlocker extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testExample(): void
    {
        $this->assertTrue(true);
    }

    public function testPrepareIpBlockerRecordsSingleIpPartial() {
        $record = ['blocked_ip' => '192.'];
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $hostingSubscription->id);

        $this->assertArrayHasKey('hosting_subscription_id', $result);
        $this->assertArrayHasKey('blocked_ip', $result);
        $this->assertArrayHasKey('beginning_ip', $result);
        $this->assertArrayHasKey('ending_ip', $result);
        $this->assertEquals($result['hosting_subscription_id'], $hostingSubscription->id);
        $this->assertEquals($result['blocked_ip'], $record['blocked_ip'] . '0.0.0/8');
        $this->assertEquals($result['beginning_ip'], $record['blocked_ip'] . '0.0.0');
        $this->assertEquals($result['ending_ip'], $record['blocked_ip'] . '255.255.255');
    }

    public function testPrepareIpBlockerRecordsSingleIp() {

        $record = ['blocked_ip' => '192.168.0.1'];
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $hostingSubscription->id);

        $this->assertArrayHasKey('hosting_subscription_id', $result);
        $this->assertArrayHasKey('blocked_ip', $result);
        $this->assertArrayHasKey('beginning_ip', $result);
        $this->assertArrayHasKey('ending_ip', $result);
        $this->assertEquals($result['hosting_subscription_id'], $hostingSubscription->id);
        $this->assertEquals($result['blocked_ip'], $record['blocked_ip']);
        $this->assertEquals($result['beginning_ip'], $record['blocked_ip']);
        $this->assertEquals($result['ending_ip'], $record['blocked_ip']);

    }

    public function testPrepareIpBlockerRecordsIpRange() {

        $record = ['blocked_ip' => '192.168.1.1-192.255.255.255'];
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $hostingSubscription->id);

        $this->assertNotEmpty($result);

        foreach($result as $res) {
            $this->assertNotEmpty($res['hosting_subscription_id']);
            $this->assertNotEmpty($res['beginning_ip']);
            $this->assertNotEmpty($res['ending_ip']);
            $this->assertArrayHasKey('hosting_subscription_id', $res);
            $this->assertArrayHasKey('blocked_ip', $res);
            $this->assertArrayHasKey('beginning_ip', $res);
            $this->assertArrayHasKey('ending_ip', $res);
            $this->assertEquals($hostingSubscription->id, $res['hosting_subscription_id']);
            $this->assertTrue(filter_var($res['beginning_ip'], FILTER_VALIDATE_IP));
            $this->assertTrue(filter_var($res['ending_ip'], FILTER_VALIDATE_IP));
            $this->assertTrue(filter_var(explode('/', $res['blocked_ip'])[0], FILTER_VALIDATE_IP));
        }

        $cidrBlocks = array_column($result, 'blocked_ip');
        $this->assertNotEmpty($cidrBlocks);

        foreach($cidrBlocks as $block) {
            $this->assertContains('/', $block);
        }

        $expectedFirstBlockedIp = '192.168.1.1';
        $expectedFirstBeginningIp = '192.168.1.1';
        $expectedFirstEndingIp = '192.168.1.1';
        $expectedLastBlockedIp = '192.192.0.0/10';
        $expectedLastBeginningIp = '192.192.0.0';
        $expectedLastEndingIp = '192.255.255.255';

        $firstRecords = reset($result);
        $this->assertEquals($expectedFirstBlockedIp, $firstRecords['blocked_ip']);
        $this->assertEquals($expectedFirstBeginningIp, $firstRecords['beginning_ip']);
        $this->assertEquals($expectedFirstEndingIp, $firstRecords['ending_ip']);

        $lastRecords = end($result);
        $this->assertEquals($expectedLastBlockedIp, $lastRecords['blocked_ip']);
        $this->assertEquals($expectedLastBeginningIp, $lastRecords['beginning_ip']);
        $this->assertEquals($expectedLastEndingIp, $lastRecords['ending_ip']);
    }

    public function testPrepareIpBlockerRecordsIpCidrBlock() {
        $record = ['blocked_ip' => '192.168.1.1/24'];
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $hostingSubscription->id);

        $this->assertNotEmpty($result);

        foreach($result as $res) {
            $this->assertArrayHasKey('hosting_subscription_id', $res);
            $this->assertArrayHasKey('blocked_ip', $res);
            $this->assertArrayHasKey('beginning_ip', $res);
            $this->assertArrayHasKey('ending_ip', $res);
            $this->assertNotEmpty($res['hosting_subscription_id']);
            $this->assertNotEmpty($res['beginning_ip']);
            $this->assertNotEmpty($res['ending_ip']);
            $this->assertTrue(filter_var($res['beginning_ip'], FILTER_VALIDATE_IP));
            $this->assertTrue(filter_var($res['ending_ip'], FILTER_VALIDATE_IP));
            $this->assertTrue(filter_var(explode('/', $res['blocked_ip'])[0], FILTER_VALIDATE_IP));
        }

        $expectedFirstBlockedIp = '192.168.1.1';
        $expectedFirstBeginningIp = '192.168.1.1';
        $expectedFirstEndingIp = '192.168.1.1';
        $expectedLastBlockedIp = '192.168.1.128/25';
        $expectedLastBeginningIp = '192.168.1.128';
        $expectedLastEndingIp = '192.168.1.255';

        $firstRecords = reset($result);
        $this->assertEquals($expectedFirstBlockedIp, $firstRecords['blocked_ip']);
        $this->assertEquals($expectedFirstBeginningIp, $firstRecords['beginning_ip']);
        $this->assertEquals($expectedFirstEndingIp, $firstRecords['ending_ip']);

        $lastRecords = end($result);
        $this->assertEquals($expectedLastBlockedIp, $lastRecords['blocked_ip']);
        $this->assertEquals($expectedLastBeginningIp, $lastRecords['beginning_ip']);
        $this->assertEquals($expectedLastEndingIp, $lastRecords['ending_ip']);
    }
}
