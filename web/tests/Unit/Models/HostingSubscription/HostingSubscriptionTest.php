<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\ApacheBuild;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Server\Helpers\PHP;
use App\Server\SupportedApplicationTypes;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class HostingSubscriptionTest extends TestCase
{
    use HasDocker;
    use HasPHP;

    public static $lastCreatedHostingSubscriptionId;

    public function testHostingSubscriptionCreation(): void
    {
        $this->installDocker();
        $this->installPHP();

        $customerUsername = 'test' . rand(1000, 9999);

        $createCustomer = new Customer();
        $createCustomer->name = $customerUsername;
        $createCustomer->email = $customerUsername . '@mail.com';
        $createCustomer->username = $customerUsername;
        $createCustomer->password = time() . rand(1000, 9999);
        $createCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $customerUsername]);

        // Test Hosting Subscription Creation with all installed PHP versions
        $getInstalledPHPVersions = PHP::getInstalledPHPVersions();
        foreach ($getInstalledPHPVersions as $phpVersion) {

            $createHostingPlan = new HostingPlan();
            $createHostingPlan->name = 'test' . rand(1000, 9999);
            $createHostingPlan->default_server_application_type = 'apache_php';
            $createHostingPlan->default_server_application_settings = [
                'php_version' => $phpVersion['full'],
                'enable_php_fpm' => true,
            ];
            $createHostingPlan->save();
            $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);

            $hostingSubscriptionService = new HostingSubscriptionService();
            $createResponse = $hostingSubscriptionService->create(
                'test' . rand(1000, 9999) . '.demo.panelomega-unit.com',
                $createCustomer->id,
                $createHostingPlan->id,
                null,
                null
            );
            $this->assertTrue($createResponse['success']);
            $hostingSubscription = $createResponse['hostingSubscription'];

            $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);

            static::$lastCreatedHostingSubscriptionId = $hostingSubscription->id;

            $findDomain = Domain::where('hosting_subscription_id', $hostingSubscription->id)->first();
            $this->assertDatabaseHas('domains', ['domain' => $findDomain->domain]);

            // Test domain php version
            shell_exec('sudo echo "127.0.0.1 '.$findDomain->domain.'" | sudo tee -a /etc/hosts');
            file_put_contents($findDomain->domain_public . '/index.php', '<?php echo "site-is-ok, "; echo phpversion(); ?>');
            $domainHomePageContent = file_get_contents('http://' . $hostingSubscription->domain);

            $this->assertTrue(Str::contains($domainHomePageContent, 'site-is-ok, ' . $phpVersion['full']));

        }
    }

//
//    public function testHostingSubscriptionDeletion(): void
//    {
//        $hostingSubscription = HostingSubscription::where('id', static::$lastCreatedHostingSubscriptionId)->first();
//        $hostingSubscription->delete();
//        $this->assertDatabaseMissing('hosting_subscriptions', ['id' => static::$lastCreatedHostingSubscriptionId]);
//    }
//
//    public function testHostingSubscriptionCreationMultiPhpVersions()
//    {
//        $customerUsername = 'test' . rand(1000, 9999);
//
//        $createCustomer = new Customer();
//        $createCustomer->name = $customerUsername;
//        $createCustomer->email = $customerUsername . '@mail.com';
//        $createCustomer->username = $customerUsername;
//        $createCustomer->password = time() . rand(1000, 9999);
//        $createCustomer->save();
//        $this->assertDatabaseHas('customers', ['username' => $customerUsername]);
//
//
//        $supportedPHPVersions = SupportedApplicationTypes::getPHPVersions();
//        foreach ($supportedPHPVersions as $phpVersion => $phpVersionName) {
//
//            $createHostingPlan = new HostingPlan();
//            $createHostingPlan->name = 'test' . rand(1000, 9999);
//            $createHostingPlan->default_server_application_type = 'apache_php';
//            $createHostingPlan->default_server_application_settings = [
//                'php_version' => $phpVersion,
//            ];
//            $createHostingPlan->save();
//            $this->assertDatabaseHas('hosting_plans', ['name' => $createHostingPlan->name]);
//
//
//            $hostingSubscription = new HostingSubscription();
//            $hostingSubscription->customer_id = $createCustomer->id;
//            $hostingSubscription->domain = 'test' . rand(1000, 9999) . '.demo.panelomega.com';
//            $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
//            $hostingSubscription->save();
//            $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);
//
//            static::$lastCreatedHostingSubscriptionId = $hostingSubscription->id;
//
//            $apacheBuild = new ApacheBuild();
//            $apacheBuild->handle();
//
//            $findDomain = Domain::where('hosting_subscription_id', $hostingSubscription->id)->first();
//            $this->assertDatabaseHas('domains', ['domain' => $findDomain->domain]);
//
            // Test domain php version
//            shell_exec('sudo echo "0.0.0.0 '.$findDomain->domain.'" | sudo tee -a /etc/hosts');
            /*            file_put_contents($findDomain->domain_public . '/index.php', '<?php echo "site-is-ok, "; echo phpversion(); ?>');*/
//            $domainHomePageContent = file_get_contents('http://' . $hostingSubscription->domain);
//            $this->assertTrue(strpos($domainHomePageContent, 'site-is-ok, '.$phpVersion) !== false);

//        }
//    }

}
