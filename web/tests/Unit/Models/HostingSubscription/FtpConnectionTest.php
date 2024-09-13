<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Models\Customer;
use App\Models\HostingPlan;
use App\Server\Helpers\PHP;
use App\Services\FtpConnections\FtpConnectionsService;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class FtpConnectionTest extends TestCase
{
    use HasDocker;
    use HasPHP;

    public function testSuccessfulLoginFtpConnection() {
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

        $testFtpServer = 'test.panelomega-ftp.com';
        $ftpUsername = $testHostingSubscription->system_username;
        $ftpPassword = 'test' . uniqid();

        $testFtpConnection = ftp_connect($testFtpServer);
        $this->assertTrue($testFtpConnection);
        $testLogin = ftp_login($testFtpConnection, $ftpUsername, $ftpPassword);
        $this->assertTrue($testLogin);

        $testFtpConnectionsService = new FtpConnectionsService();
        $testFptConnectionData = $testFtpConnectionsService::getCurrentFtpConnections();
        $this->assertNotEmpty($testFptConnectionData);
        $this->assertArrayHasKey('user', $testFptConnectionData[0]);
        $this->assertArrayHasKey('login_time', $testFptConnectionData[0]);
        $this->assertArrayHasKey('logged_in_from', $testFptConnectionData[0]);
        $this->assertArrayHasKey('status', $testFptConnectionData[0]);
        $this->assertArrayHasKey('process_id', $testFptConnectionData[0]);
    }

    public function testFailedLoginFtpConnection() {
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

        $testFtpServer = 'test.panelomega-ftp.com';
        $ftpUsername = 'test' . uniqid();
        $ftpPassword = 'test' . uniqid();

        $testFtpConnection = ftp_connect($testFtpServer);
        $this->assertTrue($testFtpConnection);
        $testLogin = ftp_login($testFtpConnection, $ftpUsername, $ftpPassword);
        $this->assertFalse($testLogin);

        $testFtpConnectionsService = new FtpConnectionsService();
        $testFtpConnectionData = $testFtpConnectionsService::getCurrentFtpConnections();
        $this->assertEmpty($testFtpConnectionData);
    }

    public function testDisconnectFtpConnection() {
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

        $testFtpServer = 'test.panelomega-ftp.com';
        $ftpUsername = $testHostingSubscription->system_username;
        $ftpPassword = 'test' . uniqid();

        $testFtpConnection = ftp_connect($testFtpServer);
        $this->assertTrue($testFtpConnection);
        $testLogin = ftp_login($testFtpConnection, $ftpUsername, $ftpPassword);
        $this->assertTrue($testLogin);

        $testFtpConnectionsService = new FtpConnectionsService();
        $testFtpConnectionData = $testFtpConnectionsService::getCurrentFtpConnections();
        $this->assertNotEmpty($testFtpConnectionData);
        $testPid = $testFtpConnectionData[0]['process_id'];

        $testDisconnectFtpAccount = $testFtpConnectionsService::disconnectFtpConnection($testPid);
        $this->assertTrue($testDisconnectFtpAccount);
    }
}
