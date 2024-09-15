<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtaccessBuildRedirects;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\Redirect;
use App\Models\Traits\RedirectTrait;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class RedirectTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;
    use RedirectTrait;

    public function testCreateRedirect()
    {
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . uniqid();
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . uniqid();
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . uniqid() . '.demo.panelomega-unit.com';
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

        $testCreateRedirectDirectory = '/testDirectory';
        $testCreateRedirectUrl = 'https://redirect_to_page.com';
        $testRedirectionsPath = "/home/{$testHostingSubscription->system_username}/public_html/.htaccess";

        $testCreateRedirect = new Redirect();
        $testCreateRedirect->type = 'permanent_301';
        $testCreateRedirect->domain = 'all_public_domains';
        $testCreateRedirect->match_www = 'redirectwithorwithoutwww';
        $testCreateRedirect->wildcard = true;
        $testCreateRedirect->directory = $testCreateRedirectDirectory;
        $testCreateRedirect->regular_expression = '';
        $testCreateRedirect->redirect_url = $testCreateRedirectUrl;
        $testCreateRedirect->save();

        $this->assertIsObject($testCreateRedirect);
        $this->assertDatabaseHas('hosting_subscription_redirects', [
            'id' => $testCreateRedirect->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $this->assertTrue(file_exists($testRedirectionsPath));
        $testHtaccessBuildRedirects = new HtaccessBuildRedirects(false, $testRedirectionsPath, $testHostingSubscription->id);
        $testGetRedirectionsData = $testHtaccessBuildRedirects->getRedirectsData();
        $this->assertNotEmpty($testGetRedirectionsData);
        $testHtaccessView = $testHtaccessBuildRedirects->getHtAccessFileConfig($testGetRedirectionsData);
        $testSystemFileContent = file_get_contents($testRedirectionsPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
    }

    public function testDeleteRedirect() {
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . uniqid();
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . uniqid();
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . uniqid() . '.demo.panelomega-unit.com';
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

        $testCreateRedirectDirectory = '/testDirectory';
        $testCreateRedirectUrl = 'https://redirect_to_page.com';
        $testRedirectionsPath = "/home/{$testHostingSubscription->system_username}/public_html/.htaccess";

        $testCreateRedirect = new Redirect();
        $testCreateRedirect->type = 'temporary_302';
        $testCreateRedirect->domain = 'all_public_domains';
        $testCreateRedirect->directory = $testCreateRedirectDirectory;
        $testCreateRedirect->regular_expression = '';
        $testCreateRedirect->redirect_url = $testCreateRedirectUrl;
        $testCreateRedirect->match_www = 'redirectwithorwithoutwww';
        $testCreateRedirect->wildcard = true;
        $testCreateRedirect->save();

        $this->assertIsObject($testCreateRedirect);

        $testCreateRedirect->delete();
        $this->assertDatabaseMissing('hosting_subscription_redirects', [
            'id' => $testCreateRedirect->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $this->assertTrue(file_exists($testRedirectionsPath));
        $testHtaccessBuildRedirects = new HtaccessBuildRedirects(false, $testRedirectionsPath, $testHostingSubscription->id);
        $testGetRedirectionsData = $testHtaccessBuildRedirects->getRedirectsData();
        $this->assertEmpty($testGetRedirectionsData);
        $testHtaccessView = $testHtaccessBuildRedirects->getHtAccessFileConfig($testGetRedirectionsData);
        $this->assertEmpty($testHtaccessView);
        $testSystemFileContent = file_get_contents($testRedirectionsPath);
        $this->assertEmpty($testSystemFileContent);
    }
}
