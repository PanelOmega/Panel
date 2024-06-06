<?php

namespace tests\Unit\Models;

use App\Jobs\ApacheBuild;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Server\SupportedApplicationTypes;
use App\Virtualization\Docker\DockerClient;
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
        $createHostingPlan->default_server_application_type = 'apache_php';
        $createHostingPlan->default_server_application_settings = json_encode([
            'php_version' => '5.6',
        ]);
        $createHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);


        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->customer_id = $createCustomer->id;
        $hostingSubscription->domain = 'test' . rand(1000, 9999) . '.phyrevoice.com';
        $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
        $hostingSubscription->save();
        $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);

        static::$lastCreatedHostingSubscriptionId = $hostingSubscription->id;

        $apacheBuild = new ApacheBuild();
        $apacheBuild->handle();

    }

    public function testHostingSubscriptionDeletion(): void
    {
        $hostingSubscription = HostingSubscription::where('id',static::$lastCreatedHostingSubscriptionId)->first();
        $hostingSubscription->delete();
        $this->assertDatabaseMissing('hosting_subscriptions', ['id' => static::$lastCreatedHostingSubscriptionId]);
    }

    public function testHostingSubscriptionCreationMultiPHPVersions()
    {
        $customerUsername = 'test' . rand(1000, 9999);

        $createCustomer = new Customer();
        $createCustomer->name = $customerUsername;
        $createCustomer->email = $customerUsername . '@mail.com';
        $createCustomer->username = $customerUsername;
        $createCustomer->password = time() . rand(1000, 9999);
        $createCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $customerUsername]);


        $supportedPHPVersions = SupportedApplicationTypes::getPHPVersions();
        foreach($supportedPHPVersions as $phpVersion=>$phpVersionName) {

            $createHostingPlan = new HostingPlan();
            $createHostingPlan->name = 'test' . rand(1000, 9999);
            $createHostingPlan->default_server_application_type = 'apache_php';
            $createHostingPlan->default_server_application_settings = json_encode([
                'php_version' => $phpVersion,
            ]);
            $createHostingPlan->save();
            $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);


            $hostingSubscription = new HostingSubscription();
            $hostingSubscription->customer_id = $createCustomer->id;
            $hostingSubscription->domain = 'test' . rand(1000, 9999) . '.phyrevoice.com';
            $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
            $hostingSubscription->save();
            $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);

            static::$lastCreatedHostingSubscriptionId = $hostingSubscription->id;

            $apacheBuild = new ApacheBuild();
            $apacheBuild->handle();

        }
    }

}
