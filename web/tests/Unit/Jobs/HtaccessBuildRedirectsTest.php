<?php

namespace tests\Unit\Jobs;

use App\Jobs\HtaccessBuildRedirects;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\Redirect;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class HtaccessBuildRedirectsTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testHandle() {
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
        $this->assertDatabaseHas('hosting_subscription_redirects', [
            'id' => $testCreateRedirect->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $this->assertFileExists($testRedirectionsPath);

        $testStartComment = '# Section managed by Panel Omega: Redirects, do not edit';
        $testEndComment = '# End section managed by Panel Omega: Redirects';
        $testExpectedCond = 'RewriteCond %{HTTP_HOST} ^.*$';
        $testExpectedRule = '^\/testDirectory$ "https\:\/\/redirect_to_page\.com" [R=302,L]';

        $testResult = file_get_contents($testRedirectionsPath);
        $this->assertTrue(str_contains($testResult, trim($testStartComment)));
        $this->assertTrue(str_contains($testResult, trim($testEndComment)));
        $this->assertTrue(str_contains($testResult, trim($testExpectedCond)));
        $this->assertTrue(str_contains($testResult, trim($testExpectedRule)));
    }

    public function testGetRedirectsData() {
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
        $this->assertDatabaseHas('hosting_subscription_redirects', [
            'id' => $testCreateRedirect->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testRecord = Redirect::where('hosting_subscription_id', $testHostingSubscription->id)->first();
        $this->assertNotNull($testRecord);

        $this->assertTrue($testRecord->domain === 'all_public_domains');
        $testExpectedCond = 'RewriteCond %{HTTP_HOST} ^.*$';

        $testEscDirectory = $testRecord->directory === '/' ? '^/$' : '^' . preg_quote($testRecord->directory, '/') . '$';
        $testPattern = '/^\^.*\\\\\\/.*\$$/';
        $this->assertMatchesRegularExpression($testPattern, $testEscDirectory);

        $testRedirectUrl = preg_quote($testRecord->redirect_url, '/');

        $testExpectedRule = "RewriteRule {$testEscDirectory} \"$testRedirectUrl\" [R={$testRecord->status_code},L]";
        $testExpectedRecord = [
            'rewriteCond' => $testExpectedCond,
            'rewriteRule' => $testExpectedRule
        ];

        $testBuilder = new HtaccessBuildRedirects(false, $testRedirectionsPath, $testHostingSubscription->id);
        $testResult = $testBuilder->getRedirectsData();

        $this->assertNotEmpty($testResult[0]);
        $this->assertArrayHasKey('rewriteCond', $testResult[0]);
        $this->assertArrayHasKey('rewriteRule', $testResult[0]);
        $this->assertEquals($testExpectedRecord, $testResult[0]);
    }

    public function testGetHtAccessFileConfig() {
        $testCond = 'RewriteCond %{HTTP_HOST} ^.*$';
        $testRule = '^\/testDirectory$ "https\:\/\/redirect_to_page\.com" [R=302,L]';
        $testHostingSubscriptionId = rand(1000, 9999);
        $testUserName = 'testUser' . uniqid();
        $testRedirectionsPath = "/home/{$testUserName}/public_html/.htaccess";

        $testRedirectRecords[] = [
            'rewriteCond' => $testCond,
            'rewriteRule' => $testRule,
        ];

        $this->assertTrue(View::exists('server.samples.apache.htaccess.redirects-htaccess'));

        $testExpectedResult = view('server.samples.apache.htaccess.redirects-htaccess', [
            'redirectsData' => $testRedirectRecords,
        ])->render();

        $testBuilder = new HtaccessBuildRedirects(false, $testRedirectionsPath, $testHostingSubscriptionId);
        $testResult = $testBuilder->getHtAccessFileConfig($testRedirectRecords);
        $this->assertNotEmpty($testResult);

        $this->assertEquals(html_entity_decode($testExpectedResult), $testResult);
    }
}
