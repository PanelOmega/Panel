<?php

namespace tests\Unit\Models;

use App\Jobs\Fail2BanConfigBuild;
use App\Models\Customer;
use App\Models\Fail2BanWhitelistedIp;
use App\Models\HostingPlan;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class Fail2BanWhitelistedIpTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;

    public function testCreateFail2BanWhitelistedIp() {
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

        $testIp = '123.10.20.30';
        $testComment = 'testComment';

        $testCreateWhitelistIp = new Fail2BanWhitelistedIp();
        $testCreateWhitelistIp->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateWhitelistIp->ip = $testIp;
        $testCreateWhitelistIp->comment = $testComment;
        $testCreateWhitelistIp->save();

        $this->assertIsObject($testCreateWhitelistIp);
        $this->assertDatabaseHas('fail2_ban_whitelisted_ips', [
            'id' => $testCreateWhitelistIp->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testPathToJailLocal = '/etc/fail2ban/jail.local';
        $this->assertTrue(file_exists($testPathToJailLocal));
        $testSystemFileContent = file_get_contents($testPathToJailLocal);
        $this->assertTrue(str_contains($testSystemFileContent, $testIp));
    }

    public function testUpdateFail2BanWhitelistedIp() {
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

        $testIp = '123.10.20.30';
        $testComment = 'testComment';

        $testCreateWhitelistIp = new Fail2BanWhitelistedIp();
        $testCreateWhitelistIp->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateWhitelistIp->ip = $testIp;
        $testCreateWhitelistIp->comment = $testComment;
        $testCreateWhitelistIp->save();

        $this->assertIsObject($testCreateWhitelistIp);
        $this->assertDatabaseHas('fail2_ban_whitelisted_ips', [
            'id' => $testCreateWhitelistIp->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testUpdateIp = '123.100.200.255';
        $testCreateWhitelistIp->update([
            'ip' => $testUpdateIp
        ]);

        $testPathToJailLocal = '/etc/fail2ban/jail.local';
        $this->assertTrue(file_exists($testPathToJailLocal));
        $testSystemFileContent = file_get_contents($testPathToJailLocal);
        $this->assertTrue(str_contains($testSystemFileContent, $testUpdateIp));
        $this->assertFalse(str_contains($testSystemFileContent, $testIp));
    }

    public function testDeleteFail2BanWhitelistedIp() {
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

        $testIp = '123.10.20.30';
        $testComment = 'testComment';

        $testCreateWhitelistIp = new Fail2BanWhitelistedIp();
        $testCreateWhitelistIp->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateWhitelistIp->ip = $testIp;
        $testCreateWhitelistIp->comment = $testComment;
        $testCreateWhitelistIp->save();

        $this->assertIsObject($testCreateWhitelistIp);
        $testCreateWhitelistIp->delete();

        $this->assertDatabaseMissing('fail2_ban_whitelisted_ips', [
            'id' => $testHostingSubscription->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testPathToJailLocal = '/etc/fail2ban/jail.local';
        $this->assertTrue(file_exists($testPathToJailLocal));
        $testSystemFileContent = file_get_contents($testPathToJailLocal);
        $this->assertFalse(str_contains($testSystemFileContent, $testIp));
    }
}
