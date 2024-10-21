<?php

namespace tests\Unit\Jobs;

use App\Jobs\ApacheBuild;
use App\Jobs\DomainPHPFPMBuild;
use App\Jobs\WebServerBuild;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class WebServerBuildTest extends TestCase
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

        $testCreateDomain = new Domain();
        $testCreateDomain->domain = $testDomain;
        $testCreateDomain->status = 'active';
        $testCreateDomain->save();

        $this->assertIsObject($testCreateDomain);
        $this->assertDatabaseHas('domains', [
            'id' => $testCreateDomain->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testDomains = Domain::whereNot('status', '<=>', 'broken')->get();
        $this->assertNotEmpty($testDomains);

        $testValues = array_column($testDomains->toArray(), 'domain');
        $this->assertTrue(in_array($testCreateDomain->domain, $testValues));

        Bus::fake();

        $testJob = new WebServerBuild();
        $testJob->handle();

        Bus::assertChained([
            new DomainPHPFPMBuild($testDomains),
            new ApacheBuild($testDomains)
        ]);
    }
}
