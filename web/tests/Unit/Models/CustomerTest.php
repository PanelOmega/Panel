<?php

namespace tests\Unit\Models;

use App\Models\Customer;
use App\Models\HostingPlan;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class CustomerTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testCreateCustomer() {
        $testPassword = time() . uniqid();
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = $testPassword;
        $testCreateCustomer->save();

        $this->assertIsObject($testCreateCustomer);
        $this->assertDatabaseHas('customers', [
            'id' => $testCreateCustomer->id,
            'username' => $testCreateCustomer->username,
        ]);
        $this->assertTrue(Hash::check($testPassword, $testCreateCustomer->password));
    }

    public function testDeleteCustomer() {
        $testPassword = time() . uniqid();
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = $testPassword;
        $testCreateCustomer->save();

        $this->assertIsObject($testCreateCustomer);
        $testCreateCustomer->delete();

        $this->assertDatabaseMissing('customers', [
            'id' => $testCreateCustomer->id,
            'username' => $testCreateCustomer->username,
        ]);
    }

    public function testGetHostingSubscriptionSession() {
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . uniqid();
        $testCreateCustomer->save();

        $this->assertIsObject($testCreateCustomer);
        $this->assertDatabaseHas('customers', [
            'id' => $testCreateCustomer->id,
            'username' => $testCreateCustomer->username
        ]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->assertNotNull(Auth::guard('customer')->user()->id);

        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
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
        $this->assertEquals($testHostingSubscription->id, Session::get('hosting_subscription_id'));
    }

    public function testCanBeImpersonated() {
        $testPassword = time() . uniqid();
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = $testPassword;
        $testCreateCustomer->save();

        $this->assertIsObject($testCreateCustomer);
        $this->assertDatabaseHas('customers', [
            'id' => $testCreateCustomer->id,
            'username' => $testCreateCustomer->username,
        ]);
        $this->assertTrue($testCreateCustomer->canBeImpersonated());
    }
}
