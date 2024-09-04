<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtaccessBuildIndexes;
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

        $testRedirectTypes = [];
        $redirectTypes = [
            'permanent_301' => 'Permanent (301)',
            'temporary_302' => 'Temporary (302)',
        ];

        foreach ($redirectTypes as $key => $type) {
            $testRedirectTypes[$key] = $type;
        }

        $testCreateRedirectDirectory = '/testDirectory';
        $testCreateRedirectUrl = 'https://redirect_to_page.com';
        $testRedirectionsPath = "/home/{$testHostingSubscription->system_username}/public_html/.htaccess";

        $testCreateRedirectObj = [];
        foreach ($testRedirectTypes as $key => $redirectType) {
            $testCreateRedirect = new Redirect();
            if($key === 'permanent_301') {
                $testCreateRedirect->type = $key;
                $testCreateRedirect->domain = 'all_public_domains';
                $testCreateRedirect->match_www = 'redirectwithorwithoutwww';
                $testCreateRedirect->wildcard = true;
            } else {
                $testCreateRedirect->type = $key;
                $testCreateRedirect->domain = $testDomain;
                $testCreateRedirect->match_www = 'only';
                $testCreateRedirect->wildcard = false;
            }
            $testCreateRedirect->directory = $testCreateRedirectDirectory;
            $testCreateRedirect->regular_expression = '';
            $testCreateRedirect->redirect_url = $testCreateRedirectUrl;
            $testCreateRedirect->save();

            $this->assertIsObject($testCreateRedirect);
            $this->assertDatabaseHas('hosting_subscription_redirects', [
                'hosting_subscription_id' => $testHostingSubscription->id,
                'status_code' => $testCreateRedirect->status_code,
                'type' => $testCreateRedirect->type,
                'domain' => $testCreateRedirect->domain,
                'directory' => $testCreateRedirect->directory,
                'redirect_url' => $testCreateRedirect->redirect_url,
                'match_www' => $testCreateRedirect->match_www,
                'wildcard' => $testCreateRedirect->wildcard
            ]);

            $testCreateRedirectObj[] = $testCreateRedirect;

            $testHtaccessBuildRedirects = new HtaccessBuildRedirects(false, $testRedirectionsPath, $testHostingSubscription->id);
            $testGetRedirectionsData = $testHtaccessBuildRedirects->getRedirectsData();
            $this->assertNotEmpty($testGetRedirectionsData);
            $testHtaccessView = $testHtaccessBuildRedirects->getHtAccessFileConfig($testGetRedirectionsData);
            $testHtaccessBuildRedirects->updateSystemFile($testRedirectionsPath, $testHtaccessView);
            $testSystemFileContent = file_get_contents($testRedirectionsPath);
            $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
        }

        foreach($testCreateRedirectObj as $obj) {
            $obj->delete();
        }
        $testCreateHostingPlan->delete();
        Session::forget('hosting_subscription_id');
        $this->assertTrue(!Session::has('hosting_subscription_id'));
        $testHostingSubscription->delete();
    }

    public function testDeleteRedirect() {
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
        $this->assertDatabaseHas('hosting_subscription_redirects', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'status_code' => $testCreateRedirect->status_code,
            'type' => $testCreateRedirect->type,
            'domain' => $testCreateRedirect->domain,
            'directory' => $testCreateRedirect->directory,
            'redirect_url' => $testCreateRedirect->redirect_url,
            'match_www' => $testCreateRedirect->match_www,
            'wildcard' => $testCreateRedirect->wildcard
        ]);

        $testCreateRedirect->delete();
        $testHtaccessBuildRedirects = new HtaccessBuildRedirects(false, $testRedirectionsPath, $testHostingSubscription->id);
        $testGetRedirectionsData = $testHtaccessBuildRedirects->getRedirectsData();
        $this->assertEmpty($testGetRedirectionsData);
        $testHtaccessView = $testHtaccessBuildRedirects->getHtAccessFileConfig($testGetRedirectionsData);
        $this->assertEmpty($testHtaccessView);
        $testHtaccessBuildRedirects->updateSystemFile($testRedirectionsPath, $testHtaccessView);
        $testSystemFileContent = file_get_contents($testRedirectionsPath);
        $this->assertEmpty($testSystemFileContent);

        $testCreateHostingPlan->delete();
        Session::forget('hosting_subscription_id');
        $this->assertTrue(!Session::has('hosting_subscription_id'));
        $testHostingSubscription->delete();
    }
}
