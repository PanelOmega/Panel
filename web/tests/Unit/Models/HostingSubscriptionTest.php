<?php

namespace tests\Unit\Models;

use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use Tests\TestCase;

class HostingSubscriptionTest extends TestCase
{
    public function testHostingSubscriptionCreation(): void
    {
        $customerUsername = 'test' . rand(1, 1000);

        $createCustomer = new Customer();
        $createCustomer->username = $customerUsername;
        $createCustomer->password = time() . rand(1, 1000);
        $createCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $customerUsername]);


        $createHostingPlan = new HostingPlan();
        $createHostingPlan->name = 'test' . rand(1, 1000);
        $createHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);

        
        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->customer_id = $createCustomer->id;
        $hostingSubscription->domain = 'test' . rand(1, 1000) . '.com';
        $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
        $hostingSubscription->save();
        $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);



    }
}
