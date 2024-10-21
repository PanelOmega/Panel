<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Models\Customer;
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

    public function testCreateZoneEditor()
    {

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
        $this->assertNotEmpty($testPhpVersion);

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

        $testCreateZoneEditor = new ZoneEditor();
        $testCreateZoneEditor->domain = $testDomain;
        $testCreateZoneEditor->name = 'testDomainName';
        $testCreateZoneEditor->ttl = '14400';
        $testCreateZoneEditor->type = 'A';
        $testCreateZoneEditor->record = '127.0.0.1';
        $testCreateZoneEditor->save();

        $this->assertIsObject($testCreateZoneEditor);
        $this->assertDatabaseHas('hosting_subscription_zone_editors', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'name' => $testCreateZoneEditor->name
        ]);

        sleep(10);

        $testConfPath = '/etc/named.conf';
        $testZoneForwardPath = "/var/named/{$testCreateZoneEditor->domain}.db";

        $this->assertTrue(is_file($testConfPath));
        $this->assertTrue(is_file($testZoneForwardPath));

        $testConfFile = file_get_contents($testConfPath);
        $this->assertNotEmpty($testConfFile);
        $this->assertTrue(str_contains($testConfFile, 'zone "' . $testCreateZoneEditor->domain . '"'));
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
        $this->assertTrue(str_contains($testZoneForwardFile, $ns1));
        $this->assertTrue(str_contains($testZoneForwardFile, "root.{$root}."));
        $this->assertTrue(str_contains($testZoneForwardFile, $ns2));
        $this->assertTrue(str_contains($digResponse, "status: NOERROR"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->domain}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->ttl}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->type}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->record}"));
        $this->assertTrue(str_contains($digResponse, "{$server}"));

        $this->assertTrue(unlink($testZoneForwardPath));
        $this->assertTrue(!file_exists($testZoneForwardPath));
    }

//    public function testCreateZoneEditorWithReverseZone()
//    {
//    }

    public function testUpdateZoneEditor()
    {
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
        $this->assertNotEmpty($testPhpVersion);

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

        $testCreateZoneEditor = new ZoneEditor();
        $testCreateZoneEditor->domain = $testDomain;
        $testCreateZoneEditor->name = 'testDomainName';
        $testCreateZoneEditor->ttl = '14400';
        $testCreateZoneEditor->type = 'A';
        $testCreateZoneEditor->record = '127.0.0.1';
        $testCreateZoneEditor->save();

        $this->assertIsObject($testCreateZoneEditor);
        $this->assertDatabaseHas('hosting_subscription_zone_editors', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'name' => $testCreateZoneEditor->name
        ]);

        sleep(10);

        $this->assertEquals('14400', $testCreateZoneEditor->ttl);

        $testUpdateRecord = '128.25.26.27';
        $testCreateZoneEditor->update([
            'record' => $testUpdateRecord,
        ]);

        $this->assertTrue($testCreateZoneEditor->record === $testUpdateRecord);

        $testConfPath = '/etc/named.conf';
        $testZoneForwardPath = "/var/named/{$testCreateZoneEditor->domain}.db";

        $this->assertTrue(is_file($testConfPath));
        $this->assertTrue(is_file($testZoneForwardPath));

        $testConfFile = file_get_contents($testConfPath);
        $this->assertNotEmpty($testConfFile);
        $this->assertTrue(str_contains($testConfFile, 'zone "' . $testCreateZoneEditor->domain . '"'));
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
        $this->assertTrue(str_contains($testZoneForwardFile, $ns1));
        $this->assertTrue(str_contains($testZoneForwardFile, "root.{$root}."));
        $this->assertTrue(str_contains($testZoneForwardFile, $ns2));
        $this->assertTrue(str_contains($digResponse, 'status: NOERROR'));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->domain}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->ttl}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->type}"));
        $this->assertTrue(str_contains($digResponse, "{$testCreateZoneEditor->record}"));
        $this->assertTrue(str_contains($digResponse, "{$server}"));

        $this->assertTrue(unlink($testZoneForwardPath));
        $this->assertTrue(!file_exists($testZoneForwardPath));
    }

    public function testDeleteZoneEditor()
    {
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
        $this->assertNotEmpty($testPhpVersion);

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

        $testCreateZoneEditor = new ZoneEditor();
        $testCreateZoneEditor->domain = $testDomain;
        $testCreateZoneEditor->name = 'testDomainName';
        $testCreateZoneEditor->ttl = '14400';
        $testCreateZoneEditor->type = 'A';
        $testCreateZoneEditor->record = '127.0.0.1';
        $testCreateZoneEditor->save();

        $testCreateZoneEditor->delete();
        $this->assertDatabaseMissing('hosting_subscription_zone_editors', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'name' => $testCreateHostingPlan->name
        ]);

        sleep(10);

        $testConfPath = '/etc/named.conf';
        $testZoneForwardPath = "/var/named/{$testCreateZoneEditor->domain}.db";

        $this->assertTrue(is_file($testConfPath));
        $this->assertTrue(is_file($testZoneForwardPath));

        $testConfFile = file_get_contents($testConfPath);
        $this->assertNotEmpty($testConfFile);

        $this->assertFalse(str_contains($testConfFile, 'zone "' . $testCreateZoneEditor->domain . '"'));
        $this->assertFalse(str_contains(trim($testConfFile), $testZoneForwardPath));

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
        $this->assertTrue(str_contains($testZoneForwardFile, $ns1));
        $this->assertTrue(str_contains($testZoneForwardFile, "root.{$root}."));
        $this->assertTrue(str_contains($testZoneForwardFile, $ns2));
        $this->assertTrue(str_contains($digResponse, 'status: NXDOMAIN'));
        $this->assertTrue(str_contains($digResponse, 'ANSWER: 0'));
        $this->assertFalse(str_contains($digResponse, "{$testCreateZoneEditor->ttl}"));
        $this->assertFalse(str_contains($digResponse, "{$testCreateZoneEditor->record}"));

        $this->assertTrue(unlink($testZoneForwardPath));
        $this->assertTrue(!file_exists($testZoneForwardPath));
    }
}
