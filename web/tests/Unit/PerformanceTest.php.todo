<?php

namespace tests\Unit;

use App\Jobs\ApacheBuild;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Server\SupportedApplicationTypes;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    public function testHostingSubscriptionCreationThousandsOfDomains()
    {
        for ($i = 0; $i < 4; $i++) {

            $customerUsername = 'testperformance' . rand(1000, 9999) . $i;

            $createCustomer = new Customer();
            $createCustomer->name = $customerUsername;
            $createCustomer->email = $customerUsername . '@mail.com';
            $createCustomer->username = $customerUsername;
            $createCustomer->password = time() . rand(1000, 9999);
            $createCustomer->save();
            $this->assertDatabaseHas('customers', ['username' => $customerUsername]);


            $supportedPHPVersions = SupportedApplicationTypes::getPHPVersions();
            foreach ($supportedPHPVersions as $phpVersion => $phpVersionName) {

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
                $hostingSubscription->domain = 'test-performance' . rand(1000, 9999) . $i . '.demo.panelomega.com';
                $hostingSubscription->hosting_plan_id = $createHostingPlan->id;
                $hostingSubscription->save();
                $this->assertDatabaseHas('hosting_subscriptions', ['domain' => $hostingSubscription->domain]);

                $apacheBuild = new ApacheBuild();
                $apacheBuild->handle();

                $findDomain = Domain::where('hosting_subscription_id', $hostingSubscription->id)->first();
                $this->assertDatabaseHas('domains', ['domain' => $findDomain->domain]);

                /*    $hostsContent = file_get_contents('/etc/hosts');
                    $hostsContent .= '127.0.0.1 ' . $hostingSubscription->domain . PHP_EOL;
                    $hostsContent .= '::1 ' . $hostingSubscription->domain . PHP_EOL;
                    file_put_contents('/etc/hosts', $hostsContent);

                    // Test domain php version
                    file_put_contents($findDomain->domain_public . '/index.php', '<?php echo "site-is-ok, "; echo phpversion(); ?>');
                    $domainHomePageContent = file_get_contents('http://' . $hostingSubscription->domain);

                    $this->assertTrue(strpos($domainHomePageContent, 'site-is-ok, ' . $phpVersion) !== false);*/

            }
        }
    }
}
