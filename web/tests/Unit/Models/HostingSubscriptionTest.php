<?php

namespace tests\Unit\Models;

use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HostingSubscriptionTest extends TestCase
{
    public static $lastCreatedHostingSubscriptionId;

    public function testHostingSubscriptionCreation(): void
    {
        $customerUsername = 'test' . rand(1000, 9999);

        $createCustomer = new Customer();
        $createCustomer->name = $customerUsername;
        $createCustomer->email = $customerUsername . '@mail.com';
        $createCustomer->username = $customerUsername;
        $createCustomer->password = time() . rand(1000, 9999);
        $createCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $customerUsername]);


        $createHostingPlan = new HostingPlan();
        $createHostingPlan->name = 'test' . rand(1000, 9999);
        $createHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);


        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->customer_id = $createCustomer->id;
        $hostingSubscription->domain = 'test' . rand(1000, 9999) . '.com';
        $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
        $hostingSubscription->save();
        $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);

        static::$lastCreatedHostingSubscriptionId = $hostingSubscription->id;

    }

//    public function testHostingSubscriptionDeletion(): void
//    {
//        $hostingSubscription = HostingSubscription::where('id',static::$lastCreatedHostingSubscriptionId)->first();
//        $hostingSubscription->delete();
//        $this->assertDatabaseMissing('hosting_subscriptions', ['id' => static::$lastCreatedHostingSubscriptionId]);
//    }

}
