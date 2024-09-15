<?php

namespace tests\Unit\Models\Traits;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\Traits\RedirectTrait;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class RedirectTraitTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use RedirectTrait;
    use DatabaseTransactions;

    public function testGetRedirectTypes() {
        $testExpected = [
            'permanent_301' => 'Permanent (301)',
            'temporary_302' => 'Temporary (302)'
        ];

        $testResult = self::getRedirectTypes();

        $this->assertEquals($testExpected, $testResult);
    }

    public function testGetWwwRedirects() {
        $testExpected = [
            'only' => 'Only redirect with www.',
            'redirectwithorwithoutwww' => 'Redirect with or without www.',
            'donotredirectwww' => 'Do Not Redirect wwww.'
        ];

        $testResult = self::getWwwRedirects();

        $this->assertEquals($testExpected, $testResult);
    }

    public function testGetRedirectDomains() {
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

        $testCreateDomain = new Domain();
        $testCreateDomain->domain = $testDomain;
        $testCreateDomain->save();

        $this->assertIsObject($testCreateDomain);
        $this->assertDatabaseHas('domains', [
            'id' => $testCreateDomain->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testExpected = [
            'all_public_domains' => 'All Public Domains',
            $testCreateDomain->domain => $testCreateDomain->domain
        ];

        $testResult = self::getRedirectDomains();

        $this->assertEquals($testExpected, $testResult);
    }
}
