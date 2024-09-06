<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtaccessBuildHotlinkProtection;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Models\HostingSubscription\HotlinkProtection;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;


class HotlinkProtectionTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;

    public function testCreateHotlinkProtection() {
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

        $testCreateHotlinkProtection = new HotlinkProtection();
        $testCreateHotlinkProtection->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateHotlinkProtection->url_allow_access = 'https://testUrl1, https://testUrl2';
        $testCreateHotlinkProtection->block_extensions = 'jpg, jpeg, png';
        $testCreateHotlinkProtection->allow_direct_requests = '0';
        $testCreateHotlinkProtection->redirect_to = 'https://testRedirectUrl';
        $testCreateHotlinkProtection->enabled = 'enabled';
        $testCreateHotlinkProtection->save();

        $this->assertIsObject($testCreateHotlinkProtection);
        $this->assertDatabaseHas('hosting_subscription_hotlink_protections', [
            'id' => $testCreateHotlinkProtection->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testHtaccessBuildHotlinkProtection= new HtaccessBuildHotlinkProtection(false, $testHostingSubscription->id);
        $testSubscription = HostingSubscription::where('id', $testHostingSubscription->id)->first();
        $testHotlinkData = $testHtaccessBuildHotlinkProtection->getHotlinkData($testSubscription->hotlinkProtection);
        $this->assertNotEmpty($testHotlinkData);
        $testHtaccessView = $testHtaccessBuildHotlinkProtection->getHtAccessFileConfig($testHotlinkData);
        $testHotlinkProtectionPath = "/home/{$testHostingSubscription->system_username}/public_html/.htaccess";
        $testHtaccessBuildHotlinkProtection->updateSystemFile($testHotlinkProtectionPath, $testHtaccessView);
        $testSystemFileContent = file_get_contents($testHotlinkProtectionPath);

        $testHtaccessView = preg_replace('/\s+/', ' ', trim($testHtaccessView));
        $testSystemFileContent = preg_replace('/\s+/', ' ', trim($testSystemFileContent));
        $this->assertTrue(str_contains($testSystemFileContent, $testHtaccessView));
    }

    public function testDeleteHotlinkProtection() {
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

        $testCreateHotlinkProtection = new HotlinkProtection();
        $testCreateHotlinkProtection->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateHotlinkProtection->url_allow_access = 'https://testUrl1, https://testUrl2';
        $testCreateHotlinkProtection->block_extensions = 'jpg, jpeg, png';
        $testCreateHotlinkProtection->allow_direct_requests = '0';
        $testCreateHotlinkProtection->redirect_to = 'https://testRedirectUrl';
        $testCreateHotlinkProtection->enabled = 'enabled';
        $testCreateHotlinkProtection->save();
        $testCreateHotlinkProtectionId = $testCreateHotlinkProtection->id;

        $this->assertIsObject($testCreateHotlinkProtection);

        $testCreateHotlinkProtection->delete();

        $this->assertDatabaseMissing('hosting_subscription_hotlink_protections', [
            'id' => $testCreateHotlinkProtectionId,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);

        $testHtaccessBuildHotlinkProtection= new HtaccessBuildHotlinkProtection(false, $testHostingSubscription->id);
        $testSubscription = HostingSubscription::where('id', $testHostingSubscription->id)->first();
        $testHotlinkData = $testHtaccessBuildHotlinkProtection->getHotlinkData($testSubscription->hotlinkProtection);
        $this->assertEmpty($testHotlinkData);
        $testHtaccessView = $testHtaccessBuildHotlinkProtection->getHtAccessFileConfig($testHotlinkData);
        $this->assertEmpty($testHtaccessView);
        $testHotlinkProtectionPath = "/home/{$testHostingSubscription->system_username}/public_html/.htaccess";
        $testHtaccessBuildHotlinkProtection->updateSystemFile($testHotlinkProtectionPath, $testHtaccessView);
        $testSystemFileContent = file_get_contents($testHotlinkProtectionPath);
        $this->assertEmpty($testSystemFileContent);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
    }

    public function testEnableHotlinkProtection() {
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

        $testCreateHotlinkProtection = new HotlinkProtection();
        $testCreateHotlinkProtection->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateHotlinkProtection->url_allow_access = 'https://testUrl1, https://testUrl2';
        $testCreateHotlinkProtection->block_extensions = 'jpg, jpeg, png';
        $testCreateHotlinkProtection->allow_direct_requests = '0';
        $testCreateHotlinkProtection->redirect_to = 'https://testRedirectUrl';
        $testCreateHotlinkProtection->enabled = 'disabled';
        $testCreateHotlinkProtection->save();

        $this->assertIsObject($testCreateHotlinkProtection);
        $this->assertDatabaseHas('hosting_subscription_hotlink_protections', [
            'id' => $testCreateHotlinkProtection->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testCreateHotlinkProtection->update([
            'enabled' => 'enabled'
        ]);

        $testHtaccessBuildHotlinkProtection= new HtaccessBuildHotlinkProtection(false, $testHostingSubscription->id);
        $testSubscription = HostingSubscription::where('id', $testHostingSubscription->id)->first();
        $testHotlinkData = $testHtaccessBuildHotlinkProtection->getHotlinkData($testSubscription->hotlinkProtection);
        $this->assertNotEmpty($testHotlinkData);
        $this->assertEquals($testHotlinkData['enabled'], 'enabled');
        $testHtaccessView = $testHtaccessBuildHotlinkProtection->getHtAccessFileConfig($testHotlinkData);
        $testHotlinkProtectionPath = "/home/{$testHostingSubscription->system_username}/public_html/.htaccess";
        $testHtaccessBuildHotlinkProtection->updateSystemFile($testHotlinkProtectionPath, $testHtaccessView);
        $testSystemFileContent = file_get_contents($testHotlinkProtectionPath);

        $testHtaccessView = preg_replace('/\s+/', ' ', trim($testHtaccessView));
        $testSystemFileContent = preg_replace('/\s+/', ' ', trim($testSystemFileContent));
        $this->assertTrue(str_contains($testSystemFileContent, $testHtaccessView));
    }

    public function testDisableHotlinkProtection() {
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

        $testCreateHotlinkProtection = new HotlinkProtection();
        $testCreateHotlinkProtection->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateHotlinkProtection->url_allow_access = 'https://testUrl1, https://testUrl2';
        $testCreateHotlinkProtection->block_extensions = 'jpg, jpeg, png';
        $testCreateHotlinkProtection->allow_direct_requests = '0';
        $testCreateHotlinkProtection->redirect_to = 'https://testRedirectUrl';
        $testCreateHotlinkProtection->enabled = 'enabled';
        $testCreateHotlinkProtection->save();

        $this->assertIsObject($testCreateHotlinkProtection);
        $this->assertDatabaseHas('hosting_subscription_hotlink_protections', [
            'id' => $testCreateHotlinkProtection->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testCreateHotlinkProtection->update([
            'enabled' => 'disabled'
        ]);

        $testHtaccessBuildHotlinkProtection= new HtaccessBuildHotlinkProtection(false, $testHostingSubscription->id);
        $testSubscription = HostingSubscription::where('id', $testHostingSubscription->id)->first();
        $testHotlinkData = $testHtaccessBuildHotlinkProtection->getHotlinkData($testSubscription->hotlinkProtection);
        $this->assertNotEmpty($testHotlinkData);
        $this->assertEquals($testHotlinkData['enabled'], 'disabled');
        $testHtaccessView = $testHtaccessBuildHotlinkProtection->getHtAccessFileConfig($testHotlinkData);
        $this->assertEmpty($testHtaccessView);
        $testHotlinkProtectionPath = "/home/{$testHostingSubscription->system_username}/public_html/.htaccess";
        $testHtaccessBuildHotlinkProtection->updateSystemFile($testHotlinkProtectionPath, $testHtaccessView);
        $testSystemFileContent = file_get_contents($testHotlinkProtectionPath);

        $testHtaccessView = preg_replace('/\s+/', ' ', trim($testHtaccessView));
        $testSystemFileContent = preg_replace('/\s+/', ' ', trim($testSystemFileContent));
        $this->assertTrue(str_contains($testSystemFileContent, $testHtaccessView));
    }
}
