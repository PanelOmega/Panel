<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtaccessBuildIpBlocker;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\IpBlocker;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class IpBlockerTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;

    public function testPrepareIpBlockerRecordsSingleIpPartial()
    {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);

        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            $testDomain,
            $testCreateCustomer->id,
            $testCreateHostingPlan->id,
            null,
            null
        );
        $this->assertTrue($createResponse['success']);
        $testHostingSubscription = $createResponse['hostingSubscription'];
        $this->assertNotEmpty($testHostingSubscription);
        Session::put('hosting_subscription_id', $testHostingSubscription->id);

        $record = ['blocked_ip' => '192.'];
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $testHostingSubscription->id);

        $this->assertArrayHasKey('hosting_subscription_id', $result[0]);
        $this->assertArrayHasKey('blocked_ip', $result[0]);
        $this->assertArrayHasKey('beginning_ip', $result[0]);
        $this->assertArrayHasKey('ending_ip', $result[0]);
        $this->assertEquals($result[0]['hosting_subscription_id'], $testHostingSubscription->id);
        $this->assertEquals($result[0]['blocked_ip'], $record['blocked_ip'] . '0.0.0/8');
        $this->assertEquals($result[0]['beginning_ip'], $record['blocked_ip'] . '0.0.0');
        $this->assertEquals($result[0]['ending_ip'], $record['blocked_ip'] . '255.255.255');

        $testCreateIpBlockerRecord = new IpBlocker();
        $testCreateIpBlockerRecord->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateIpBlockerRecord->blocked_ip = $result[0]['blocked_ip'];
        $testCreateIpBlockerRecord->beginning_ip = $result[0]['beginning_ip'];
        $testCreateIpBlockerRecord->ending_ip = $result[0]['ending_ip'];
        $testCreateIpBlockerRecord->save();

        $this->assertIsObject($testCreateIpBlockerRecord);
        $this->assertDatabaseHas('hosting_subscription_ip_blockers', [
            'id' => $testCreateIpBlockerRecord->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);

        $testSystemUsername = $testHostingSubscription->system_username;
        $testIpBlockedPath = "/home/{$testSystemUsername}/public_html/.htaccess";
        $this->assertTrue(file_exists($testIpBlockedPath));

        $testHtAccessBuild = new HtaccessBuildIpBlocker(false, $testIpBlockedPath, $testHostingSubscription->id);

        $testBlockedIps = $testHtAccessBuild->getAllBlockedIps();
        $this->assertNotEmpty($testBlockedIps);
        $testHtAccessView = $testHtAccessBuild->getHtaccessIpBlockerConfig($testBlockedIps);
        foreach ($testBlockedIps as $testBlockedIp) {
            $this->assertNotEmpty($testBlockedIp);
            $this->assertTrue(str_contains($testHtAccessView, $testBlockedIp));
        }

        $testSystemFileContent = file_get_contents($testIpBlockedPath);
        $this->assertNotEmpty($testSystemFileContent);
        $this->assertNotFalse($this->assertStringContainsString($testHtAccessView, $testSystemFileContent, false));
    }

    public function testPrepareIpBlockerRecordsSingleIp()
    {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);

        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            $testDomain,
            $testCreateCustomer->id,
            $testCreateHostingPlan->id,
            null,
            null
        );
        $this->assertTrue($createResponse['success']);
        $testHostingSubscription = $createResponse['hostingSubscription'];
        $this->assertNotEmpty($testHostingSubscription);
        Session::put('hosting_subscription_id', $testHostingSubscription->id);

        $record = ['blocked_ip' => '192.168.0.1'];
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $testHostingSubscription->id);
        $this->assertArrayHasKey('hosting_subscription_id', $result[0]);
        $this->assertArrayHasKey('blocked_ip', $result[0]);
        $this->assertArrayHasKey('beginning_ip', $result[0]);
        $this->assertArrayHasKey('ending_ip', $result[0]);
        $this->assertEquals($result[0]['hosting_subscription_id'], $testHostingSubscription->id);
        $this->assertEquals($result[0]['blocked_ip'], $record['blocked_ip']);
        $this->assertEquals($result[0]['beginning_ip'], $record['blocked_ip']);
        $this->assertEquals($result[0]['ending_ip'], $record['blocked_ip']);

        $testCreateIpBlockerRecord = new IpBlocker();
        $testCreateIpBlockerRecord->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateIpBlockerRecord->blocked_ip = $result[0]['blocked_ip'];
        $testCreateIpBlockerRecord->beginning_ip = $result[0]['beginning_ip'];
        $testCreateIpBlockerRecord->ending_ip = $result[0]['ending_ip'];
        $testCreateIpBlockerRecord->save();

        $this->assertIsObject($testCreateIpBlockerRecord);
        $this->assertDatabaseHas('hosting_subscription_ip_blockers', [
            'id' => $testCreateIpBlockerRecord->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);
        $testSystemUsername = $testHostingSubscription->system_username;
        $testIpBlockedPath = "/home/{$testSystemUsername}/public_html/.htaccess";
        $this->assertTrue(file_exists($testIpBlockedPath));

        $testHtAccessBuild = new HtaccessBuildIpBlocker(false, $testIpBlockedPath, $testHostingSubscription->id);

        $testBlockedIps = $testHtAccessBuild->getAllBlockedIps();
        $this->assertNotEmpty($testBlockedIps);
        $testHtAccessView = $testHtAccessBuild->getHtaccessIpBlockerConfig($testBlockedIps);
        foreach ($testBlockedIps as $testBlockedIp) {
            $this->assertNotEmpty($testBlockedIp);
            $this->assertTrue(str_contains($testHtAccessView, $testBlockedIp));
        }

        $testSystemFileContent = file_get_contents($testIpBlockedPath);
        $this->assertNotEmpty($testSystemFileContent);
        $this->assertNotFalse($this->assertStringContainsString($testHtAccessView, $testSystemFileContent, false));
    }

    public function testPrepareIpBlockerRecordsIpRange()
    {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);

        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            $testDomain,
            $testCreateCustomer->id,
            $testCreateHostingPlan->id,
            null,
            null
        );
        $this->assertTrue($createResponse['success']);
        $testHostingSubscription = $createResponse['hostingSubscription'];
        $this->assertNotEmpty($testHostingSubscription);
        Session::put('hosting_subscription_id', $testHostingSubscription->id);

        $record = ['blocked_ip' => '192.168.1.1-192.255.255.255'];
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $testHostingSubscription->id);

        foreach ($result as $key => $ip) {
            $this->assertArrayHasKey('hosting_subscription_id', $ip);
            $this->assertArrayHasKey('blocked_ip', $ip);
            $this->assertArrayHasKey('beginning_ip', $ip);
            $this->assertArrayHasKey('ending_ip', $ip);
            $this->assertEquals($testHostingSubscription->id, $ip['hosting_subscription_id']);
            $this->assertNotFalse(filter_var($ip['beginning_ip'], FILTER_VALIDATE_IP));
            $this->assertNotFalse(filter_var($ip['ending_ip'], FILTER_VALIDATE_IP));
            $this->assertNotFalse(filter_var(explode('/', $ip['blocked_ip'])[0], FILTER_VALIDATE_IP));

            $testCreateIpBlockerRecord = new IpBlocker();
            $testCreateIpBlockerRecord->hosting_subscription_id = $testHostingSubscription->id;
            $testCreateIpBlockerRecord->blocked_ip = $ip['blocked_ip'];
            $testCreateIpBlockerRecord->beginning_ip = $ip['beginning_ip'];
            $testCreateIpBlockerRecord->ending_ip = $ip['ending_ip'];
            $testCreateIpBlockerRecord->save();

            $this->assertIsObject($testCreateIpBlockerRecord);
            $this->assertDatabaseHas('hosting_subscription_ip_blockers', [
                'id' => $testCreateIpBlockerRecord->id,
                'hosting_subscription_id' => $testHostingSubscription->id
            ]);
        }

        $testSystemUsername = $testHostingSubscription->system_username;
        $testIpBlockedPath = "/home/{$testSystemUsername}/public_html/.htaccess";
        $this->assertTrue(file_exists($testIpBlockedPath));

        $testHtAccessBuild = new HtaccessBuildIpBlocker(false, $testIpBlockedPath, $testHostingSubscription->id);

        $testBlockedIps = $testHtAccessBuild->getAllBlockedIps();
        $this->assertNotEmpty($testBlockedIps);
        $testHtAccessView = $testHtAccessBuild->getHtaccessIpBlockerConfig($testBlockedIps);
        foreach ($testBlockedIps as $testBlockedIp) {
            $this->assertNotEmpty($testBlockedIp);
            $this->assertTrue(str_contains($testHtAccessView, $testBlockedIp));
        }

        $testSystemFileContent = file_get_contents($testIpBlockedPath);
        $this->assertNotEmpty($testSystemFileContent);
        $this->assertNotFalse($this->assertStringContainsString($testHtAccessView, $testSystemFileContent, false));
    }

    public function testPrepareIpBlockerRecordsIpCidrBlock()
    {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);

        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            $testDomain,
            $testCreateCustomer->id,
            $testCreateHostingPlan->id,
            null,
            null
        );
        $this->assertTrue($createResponse['success']);
        $testHostingSubscription = $createResponse['hostingSubscription'];
        $this->assertNotEmpty($testHostingSubscription);
        Session::put('hosting_subscription_id', $testHostingSubscription->id);

        $record = ['blocked_ip' => '192.168.1.1/24'];
        $result = \App\Models\HostingSubscription\IpBlocker::prepareIpBlockerRecords($record, $testHostingSubscription->id);

        foreach ($result as $key => $ip) {
            $this->assertArrayHasKey('hosting_subscription_id', $ip);
            $this->assertArrayHasKey('blocked_ip', $ip);
            $this->assertArrayHasKey('beginning_ip', $ip);
            $this->assertArrayHasKey('ending_ip', $ip);
            $this->assertEquals($testHostingSubscription->id, $ip['hosting_subscription_id']);
            $this->assertNotFalse(filter_var($ip['beginning_ip'], FILTER_VALIDATE_IP));
            $this->assertNotFalse(filter_var($ip['ending_ip'], FILTER_VALIDATE_IP));
            $this->assertNotFalse(filter_var(explode('/', $ip['blocked_ip'])[0], FILTER_VALIDATE_IP));

            $testCreateIpBlockerRecord = new IpBlocker();
            $testCreateIpBlockerRecord->hosting_subscription_id = $testHostingSubscription->id;
            $testCreateIpBlockerRecord->blocked_ip = $ip['blocked_ip'];
            $testCreateIpBlockerRecord->beginning_ip = $ip['beginning_ip'];
            $testCreateIpBlockerRecord->ending_ip = $ip['ending_ip'];
            $testCreateIpBlockerRecord->save();

            $this->assertIsObject($testCreateIpBlockerRecord);
            $this->assertDatabaseHas('hosting_subscription_ip_blockers', [
                'id' => $testCreateIpBlockerRecord->id,
                'hosting_subscription_id' => $testHostingSubscription->id,
            ]);
        }

        $testSystemUsername = $testHostingSubscription->system_username;
        $testIpBlockedPath = "/home/{$testSystemUsername}/public_html/.htaccess";
        $this->assertTrue(is_file($testIpBlockedPath));

        $testHtAccessBuild = new HtaccessBuildIpBlocker(false, $testIpBlockedPath, $testHostingSubscription->id);

        $testBlockedIps = $testHtAccessBuild->getAllBlockedIps();
        $this->assertNotEmpty($testBlockedIps);
        $testHtAccessView = $testHtAccessBuild->getHtaccessIpBlockerConfig($testBlockedIps);
        foreach ($testBlockedIps as $testBlockedIp) {
            $this->assertNotEmpty($testBlockedIp);
            $this->assertTrue(str_contains($testHtAccessView, $testBlockedIp));
        }

        $testSystemFileContent = file_get_contents($testIpBlockedPath);
        $this->assertNotEmpty($testSystemFileContent);
        $this->assertNotFalse($this->assertStringContainsString($testHtAccessView, $testSystemFileContent, false));
    }
}
