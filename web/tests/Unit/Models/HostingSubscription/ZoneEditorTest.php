<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\ZoneEditor;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class ZoneEditorTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testCreateZoneEditor() {

//        Customer::where('email', ' testCustomer@mail.com')->delete();
//        HostingPlan::where('name', 'testHostingPlan')->delete();
//        Domain::where('domain', 'test.demo.panelomega-unit.com')->delete();
//        dd();

        $testCustomerUsername = 'testCustomer';
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
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'testHostingPlan';
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test.demo.panelomega-unit.com';
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

        $testCreateZoneEditor = new ZoneEditor();
        $testCreateZoneEditor->domain = $testDomain;
        $testCreateZoneEditor->name = 'testDomainName';
        $testCreateZoneEditor->ttl = '14400';
        $testCreateZoneEditor->type = 'A';
        $testCreateZoneEditor->record = '127.0.0.1';
        $testCreateZoneEditor->save();

        sleep(5);

        $this->assertIsObject($testCreateZoneEditor);
        $this->assertDatabaseHas('hosting_subscription_zone_editors', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'name' => $testCreateZoneEditor->name
        ]);

        $this->assertEquals('14400', '14400');
        $testConfPath = '/etc/named.conf';
        $testZoneForwardPath = "/var/named/test.demo.panelomega-unit.com.db";

        $this->assertTrue(is_file($testConfPath));
        $this->assertTrue(is_file($testZoneForwardPath));

        $testConfFile = file_get_contents($testConfPath);
        $this->assertNotEmpty($testConfFile);
        $this->assertTrue(str_contains($testConfFile, 'zone "test.demo.panelomega-unit.com"'));
        $this->assertTrue(str_contains(trim($testConfFile), $testZoneForwardPath));

        $testZoneForwardFile = file_get_contents($testZoneForwardPath);
        $this->assertNotEmpty($testZoneForwardFile);
        $ns1 = setting('general.ns1');
        $ns2 = setting('general.ns2');
        $rootServer = explode('.', $ns1);
        array_shift($rootServer);
        $root = implode('.', $rootServer);

        $digResponse = shell_exec('dig ' . $testCreateZoneEditor->domain);
        $this->assertTrue(file_exists($testZoneForwardPath));

        $serverResponse = shell_exec('hostname -I | awk \'{print $1}\'');
        $server = trim($serverResponse);

        $this->assertTrue(str_contains($testZoneForwardFile, "ns1.{$ns1}."));
        $this->assertTrue(str_contains($testZoneForwardFile, "root.{$root}."));
        $this->assertTrue(str_contains($testZoneForwardFile, "ns2.{$ns2}."));
        $this->assertTrue(str_contains($digResponse, "status: NOERROR"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->domain}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->ttl}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->type}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->record}"));
        $this->assertTrue(str_contains($digResponse, "{$server}"));

        if(file_exists($testZoneForwardPath)) {
            unlink($testZoneForwardPath);
        }
        $this->assertTrue(!file_exists($testZoneForwardPath));
    }

//    public function testCreateZoneEditorWithReverseZone()
//    {
//    }

//    public function testUpdateZoneEditor()
//    {
//        $testCustomerUsername = 'test' . uniqid();
//        $testCreateCustomer = new Customer();
//        $testCreateCustomer->name = $testCustomerUsername;
//        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
//        $testCreateCustomer->username = $testCustomerUsername;
//        $testCreateCustomer->password = time() . uniqid();
//        $testCreateCustomer->save();
//        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);
//
//        Auth::guard('customer')->login($testCreateCustomer);
//        $this->installDocker();
//        $this->installPHP();
//
//        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
//        $this->assertNotEmpty($testPhpVersion);
//
//        $testCreateHostingPlan = new HostingPlan();
//        $testCreateHostingPlan->name = 'test' . uniqid();
//        $testCreateHostingPlan->default_server_application_type = 'apache_php';
//        $testCreateHostingPlan->default_server_application_settings = [
//            'php_version' => $testPhpVersion,
//            'enable_php_fpm' => true,
//        ];
//        $testCreateHostingPlan->save();
//        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);
//
//        $testDomain = 'test' . uniqid() . '.demo.panelomega-unit.com';
//        $hostingSubscriptionService = new HostingSubscriptionService();
//        $createResponse = $hostingSubscriptionService->create(
//            $testDomain,
//            $testCreateCustomer->id,
//            $testCreateHostingPlan->id,
//            null,
//            null
//        );
//        $this->assertTrue($createResponse['success']);
//        $testHostingSubscription = $createResponse['hostingSubscription'];
//        $this->assertNotEmpty($testHostingSubscription);
//        Session::put('hosting_subscription_id', $testHostingSubscription->id);
//
//        $testCreateZoneEditor = new ZoneEditor();
//        $testCreateZoneEditor->domain = $testDomain;
//        $testCreateZoneEditor->name = 'testDomainName';
//        $testCreateZoneEditor->type = 'A';
//        $testCreateZoneEditor->record = '127.0.0.1';
//        $testCreateZoneEditor->save();
//
//        $this->assertIsObject($testCreateZoneEditor);
//        $this->assertDatabaseHas('hosting_subscription_zone_editors', [
//            'hosting_subscription_id' => $testHostingSubscription->id,
//            'name' => $testCreateZoneEditor->name
//        ]);
//
//        $this->assertEquals('14400', $testCreateZoneEditor->ttl);
//
//        $testUpdateRecord = '128.25.26.27';
//        $testCreateZoneEditor->update([
//            'record' => $testUpdateRecord,
//        ]);
//
//        $this->assertTrue($testCreateZoneEditor->record === $testUpdateRecord);
//
//        $testConfPath = '/etc/named.conf';
//        $testDnsZonesPath = '/etc/named.panelomega.zones';
//        $testZoneForwardPath = "/etc/named.{$testCreateZoneEditor->domain}.db";
//
//        $this->assertTrue(is_file($testConfPath));
//        $this->assertTrue(is_file($testDnsZonesPath));
//        $this->assertTrue(is_file($testZoneForwardPath));
//
//        $testConfFile = file_get_contents($testConfPath);
//        $this->assertNotEmpty($testConfFile);
//        $this->assertTrue(str_contains($testConfFile, $testDnsZonesPath));
//
//        $testDnsZonesFile = file_get_contents($testDnsZonesPath);
//        $this->assertNotEmpty($testDnsZonesFile);
//        $this->assertTrue(str_contains($testDnsZonesFile, $testCreateZoneEditor->domain));
//        $this->assertTrue(str_contains($testDnsZonesFile, $testZoneForwardPath));
//
//        $testZoneForwardFile = file_get_contents($testZoneForwardPath);
//        $this->assertNotEmpty($testZoneForwardFile);
//        $this->assertTrue(str_contains($testZoneForwardFile, "ns1.{$testCreateZoneEditor->domain}."));
//        $this->assertTrue(str_contains($testZoneForwardFile, "admin.{$testCreateZoneEditor->domain}."));
//        $testARecord = preg_replace('/\s+/', '\s+', preg_quote("{$testCreateZoneEditor->domain}. IN A {$testUpdateRecord}"));
//        $this->assertTrue((bool)preg_match("/$testARecord/", preg_replace('/\s+/', ' ', $testZoneForwardFile)));
//
//        if(file_exists($testZoneForwardPath)) {
//            unlink($testZoneForwardPath);
//        }
//        $this->assertTrue(!file_exists($testZoneForwardPath));
//    }
//
//    public function testDeleteZoneEditor()
//    {
//        $testCustomerUsername = 'test' . uniqid();
//        $testCreateCustomer = new Customer();
//        $testCreateCustomer->name = $testCustomerUsername;
//        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
//        $testCreateCustomer->username = $testCustomerUsername;
//        $testCreateCustomer->password = time() . uniqid();
//        $testCreateCustomer->save();
//        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);
//
//        Auth::guard('customer')->login($testCreateCustomer);
//        $this->installDocker();
//        $this->installPHP();
//
//        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
//        $this->assertNotEmpty($testPhpVersion);
//
//        $testCreateHostingPlan = new HostingPlan();
//        $testCreateHostingPlan->name = 'test' . uniqid();
//        $testCreateHostingPlan->default_server_application_type = 'apache_php';
//        $testCreateHostingPlan->default_server_application_settings = [
//            'php_version' => $testPhpVersion,
//            'enable_php_fpm' => true,
//        ];
//        $testCreateHostingPlan->save();
//        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);
//
//        $testDomain = 'test' . uniqid() . '.demo.panelomega-unit.com';
//        $hostingSubscriptionService = new HostingSubscriptionService();
//        $createResponse = $hostingSubscriptionService->create(
//            $testDomain,
//            $testCreateCustomer->id,
//            $testCreateHostingPlan->id,
//            null,
//            null
//        );
//        $this->assertTrue($createResponse['success']);
//        $testHostingSubscription = $createResponse['hostingSubscription'];
//        $this->assertNotEmpty($testHostingSubscription);
//        Session::put('hosting_subscription_id', $testHostingSubscription->id);
//
//        $testCreateZoneEditor = new ZoneEditor();
//        $testCreateZoneEditor->domain = $testDomain;
//        $testCreateZoneEditor->name = 'testDomainName';
//        $testCreateZoneEditor->type = 'A';
//        $testCreateZoneEditor->record = '127.0.0.1';
//        $testCreateZoneEditor->save();
//
//        $testCreateZoneEditor->delete();
//        $this->assertDatabaseMissing('hosting_subscription_zone_editors', [
//            'hosting_subscription_id' => $testHostingSubscription->id,
//            'name' => $testCreateHostingPlan->name
//        ]);
//
//        $testConfPath = '/etc/named.conf';
//        $testDnsZonesPath = '/etc/named.panelomega.zones';
//        $testZoneForwardPath = "/etc/named.{$testCreateZoneEditor->domain}.db";
//
//        $this->assertTrue(is_file($testConfPath));
//        $this->assertTrue(is_file($testDnsZonesPath));
//        $this->assertTrue(is_file($testZoneForwardPath));
//
//        $testDnsZonesFile = file_get_contents($testDnsZonesPath);
//        $this->assertEmpty($testDnsZonesFile);
//        $this->assertTrue(!str_contains($testDnsZonesFile, $testCreateZoneEditor->domain));
//        $this->assertTrue(!str_contains($testDnsZonesFile, $testZoneForwardPath));
//
//        $testZoneForwardFile = file_get_contents($testZoneForwardPath);
//        $this->assertNotEmpty($testZoneForwardFile);
//        $this->assertTrue(str_contains($testZoneForwardFile, "ns1.{$testCreateZoneEditor->domain}."));
//        $this->assertTrue(str_contains($testZoneForwardFile, "admin.{$testCreateZoneEditor->domain}."));
//        $testARecord = preg_replace('/\s+/', '\s+', preg_quote("{$testCreateZoneEditor->domain}. IN A {$testCreateZoneEditor->record}"));
//        $this->assertTrue(!preg_match("/$testARecord/", preg_replace('/\s+/', ' ', $testZoneForwardFile)));
//
//        if(file_exists($testZoneForwardPath)) {
//            unlink($testZoneForwardPath);
//        }
//        $this->assertTrue(!file_exists($testZoneForwardPath));
//    }
}
