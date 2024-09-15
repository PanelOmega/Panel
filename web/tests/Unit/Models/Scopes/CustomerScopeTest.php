<?php

namespace tests\Unit\Models\Traits;

use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\Scopes\CustomerScope;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class CustomerScopeTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testApplyWithAuthentication() {
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . uniqid();
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $testAuthGuard = Auth::guard('customer');

        $testHostingSubscriptionId = rand(1, 100);
        Session::put('hosting_subscription_id', $testHostingSubscriptionId);

        $this->assertTrue($testAuthGuard->check());
        $this->assertEquals($testCreateCustomer->id, $testAuthGuard->id());
        $testHostingSubscriptionId = Session::get('hosting_subscription_id');
        $this->assertEquals($testHostingSubscriptionId, $testHostingSubscriptionId);

        $testBuilder = \Mockery::mock(Builder::class);
        $testQuery = \Mockery::mock();
        $testQuery->shouldReceive('where')
            ->with('customer_id', $testCreateCustomer->id)
            ->once()
            ->andReturnSelf();
        $testQuery->shouldReceive('where')
            ->with('id', $testHostingSubscriptionId)
            ->once()
            ->andReturnSelf();

        $testBuilder->shouldReceive('whereHas')
            ->with('hostingSubscription', \Mockery::on(function ($callback) use ($testQuery) {
                $callback($testQuery);
                return true;
            }))
            ->once();

        $testCustomerScope = new CustomerScope();
        $testModel = new class extends Model {};
        $testCustomerScope->apply($testBuilder, $testModel);

        $testBuilder->shouldHaveReceived('whereHas')->once();
    }

    public function testApplyWithNoAuthentication() {

        $mockAuthGuard = \Mockery::mock();
        $mockAuthGuard->shouldReceive('check')->andReturn(false);
        Auth::shouldReceive('guard')->with('customer')->andReturn($mockAuthGuard);

        $testBuilder = \Mockery::mock(Builder::class);
        $testBuilder->shouldNotReceive('whereHas');

        $testCustomerScope = new CustomerScope();
        $testModel = new class extends Model {};

        $testCustomerScope->apply($testBuilder, $testModel);
        $testBuilder->shouldNotHaveReceived('whereHas');
    }
}
