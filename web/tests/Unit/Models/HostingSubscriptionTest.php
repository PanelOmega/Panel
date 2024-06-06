<?php

namespace tests\Unit\Models;

use App\Jobs\ApacheBuild;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Virtualization\Docker\DockerClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HostingSubscriptionTest extends TestCase
{
    public static $lastCreatedHostingSubscriptionId;

    public function testHostingSubscriptionCreation(): void
    {
//        Schema::table('hosting_plans', function (Blueprint $table) {
//            $table->string('default_server_application_type')->nullable()->default('apache_php');
//            $table->longText('default_server_application_settings')->nullable();
//        });

//        Schema::table('domains', function (Blueprint $table) {
//            $table->longText('docker_settings')->nullable();
//        });

//        $docker = new DockerClient();
//        dd($docker->listContainers());
//
//        die();
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
        $createHostingPlan->default_server_application_type = 'docker_apache_php';
        $createHostingPlan->default_server_application_settings = json_encode([
            'php_version' => '7.4',
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

        dd($hostingSubscription->domain);
    }

//    public function testHostingSubscriptionDeletion(): void
//    {
//        $hostingSubscription = HostingSubscription::where('id',static::$lastCreatedHostingSubscriptionId)->first();
//        $hostingSubscription->delete();
//        $this->assertDatabaseMissing('hosting_subscriptions', ['id' => static::$lastCreatedHostingSubscriptionId]);
//    }

}
