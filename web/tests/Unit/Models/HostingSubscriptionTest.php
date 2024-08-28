<?php

namespace tests\Unit\Models;

use App\Jobs\ApacheBuild;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Server\Fail2ban;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;

class HostingSubscriptionTest extends TestCase
{
    use HasDocker;

    public static $lastCreatedHostingSubscriptionId;

    public function testHostingSubscriptionCreation(): void
    {
        $this->installDocker();

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
        $createHostingPlan->default_server_application_settings = [
            'php_version' => '5.6',
        ];
        $createHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);


        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->customer_id = $createCustomer->id;
        $hostingSubscription->domain = 'test' . rand(1000, 9999) . '.demo.panelomega.com';
        $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
        $hostingSubscription->save();
        $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);

        static::$lastCreatedHostingSubscriptionId = $hostingSubscription->id;

        $apacheBuild = new ApacheBuild();
        $apacheBuild->handle();

        $findDomain = Domain::where('hosting_subscription_id', $hostingSubscription->id)->first();
        $this->assertDatabaseHas('domains', ['domain' => $findDomain->domain]);

        // Test domain php version
//        shell_exec('sudo echo "0.0.0.0 '.$findDomain->domain_public.'" | sudo tee -a /etc/hosts');
/*        file_put_contents($findDomain->domain_public . '/index.php', '<?php echo "site-is-ok, "; echo phpversion(); ?>');*/
//        $domainHomePageContent = file_get_contents('http://' . $hostingSubscription->domain);
//        $this->assertTrue(Str::contains($domainHomePageContent, 'site-is-ok, 5.6'));

    }

    public function testHostingSubscriptionDeletion(): void
    {
        $hostingSubscription = HostingSubscription::where('id',static::$lastCreatedHostingSubscriptionId)->first();
        $hostingSubscription->delete();
        $this->assertDatabaseMissing('hosting_subscriptions', ['id' => static::$lastCreatedHostingSubscriptionId]);
    }

    public function testHostingSubscriptionCreationMultiPhpVersions()
    {
        $customerUsername = 'test' . rand(1000, 9999);

        $createCustomer = new Customer();
        $createCustomer->name = $customerUsername;
        $createCustomer->email = $customerUsername . '@mail.com';
        $createCustomer->username = $customerUsername;
        $createCustomer->password = time() . rand(1000, 9999);
        $createCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $customerUsername]);


        $supportedPHPVersions = Fail2ban::getPHPVersions();
        foreach($supportedPHPVersions as $phpVersion=>$phpVersionName) {

            $createHostingPlan = new HostingPlan();
            $createHostingPlan->name = 'test' . rand(1000, 9999);
            $createHostingPlan->default_server_application_type = 'apache_php';
            $createHostingPlan->default_server_application_settings = [
                'php_version' => $phpVersion,
            ];
            $createHostingPlan->save();
            $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);


            $hostingSubscription = new HostingSubscription();
            $hostingSubscription->customer_id = $createCustomer->id;
            $hostingSubscription->domain = 'test' . rand(1000, 9999) . '.demo.panelomega.com';
            $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
            $hostingSubscription->save();
            $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);

            static::$lastCreatedHostingSubscriptionId = $hostingSubscription->id;

            $apacheBuild = new ApacheBuild();
            $apacheBuild->handle();

            $findDomain = Domain::where('hosting_subscription_id', $hostingSubscription->id)->first();
            $this->assertDatabaseHas('domains', ['domain' => $findDomain->domain]);

            // Test domain php version
//            shell_exec('sudo echo "0.0.0.0 '.$findDomain->domain_public.'" | sudo tee -a /etc/hosts');
/*            file_put_contents($findDomain->domain_public . '/index.php', '<?php echo "site-is-ok, "; echo phpversion(); ?>');*/
//            $domainHomePageContent = file_get_contents('http://' . $hostingSubscription->domain);
//            $this->assertTrue(strpos($domainHomePageContent, 'site-is-ok, '.$phpVersion) !== false);

        }
    }

}
