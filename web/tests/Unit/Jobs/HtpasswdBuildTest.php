<?php

namespace tests\Unit\Jobs;

use App\Jobs\HtpasswdBuild;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\HtpasswdUser;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class HtpasswdBuildTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testHandle() {
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

        $testUsername = 'testUsername' . uniqid();
        $testPassword = 'testPassword' . uniqid();
        $testDirectory = "/home/{$testHostingSubscription->system_username}/public_html";
        $testDirectoryRealPath = "/home/{$testHostingSubscription->system_username}/.htpasswd";

        $testCreateHtpasswdUser = new HtpasswdUser();
        $testCreateHtpasswdUser->directory = $testDirectory;
        $testCreateHtpasswdUser->username = $testUsername;
        $testCreateHtpasswdUser->password = $testPassword;
        $testCreateHtpasswdUser->save();

        $this->assertIsObject($testCreateHtpasswdUser);
        $this->assertDatabaseHas('hosting_subscription_htpasswd_users', [
            'id' => $testCreateHtpasswdUser->id
        ]);

        $testStartComment = '# Section managed by Panel Omega: Directory Privacy, do not edit';
        $testEndComment = '# End section managed by Panel Omega: Directory Privacy';
        $testExpectedResult = "{$testCreateHtpasswdUser->username}:{$testCreateHtpasswdUser->password}";

        $this->assertTrue(file_exists($testDirectoryRealPath));
        $testResult = file_get_contents($testDirectoryRealPath);
        $this->assertNotEmpty($testResult);

        $this->assertTrue(str_contains($testResult, trim($testStartComment)));
        $this->assertTrue(str_contains($testResult, trim($testEndComment)));
        $this->assertTrue(str_contains($testResult, trim($testExpectedResult)));
    }

    public function testGetHtPasswdRecords() {
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

        $testUsername = 'testUsername' . uniqid();
        $testPassword = 'testPassword' . uniqid();
        $testDirectory = "/home/{$testHostingSubscription->system_username}/public_html";
        $testDirectoryRealPath = "/home/{$testHostingSubscription->system_username}/.htpasswd";

        $testCreateHtpasswdUser = new HtpasswdUser();
        $testCreateHtpasswdUser->directory = $testDirectory;
        $testCreateHtpasswdUser->username = $testUsername;
        $testCreateHtpasswdUser->password = $testPassword;
        $testCreateHtpasswdUser->save();

        $this->assertIsObject($testCreateHtpasswdUser);
        $this->assertDatabaseHas('hosting_subscription_htpasswd_users', [
            'id' => $testCreateHtpasswdUser->id
        ]);

        $testHtPasswdData = [
            'username' => $testCreateHtpasswdUser->username,
            'password' => $testCreateHtpasswdUser->password
        ];

        $this->assertFileExists($testDirectoryRealPath);

        $testExpectedResult = [
            "{$testCreateHtpasswdUser->username}:{$testCreateHtpasswdUser->password}"
        ];

        $testResult = new HtpasswdBuild(false, $testDirectoryRealPath, $testHtPasswdData);
        $this->assertEquals($testExpectedResult[0], $testResult->getHtPasswdRecords($testHtPasswdData)[0]);
    }

    public function testGetHtPasswdFileConfig() {
        $testUsername = 'testUsername' . uniqid();
        $testPassword = Hash::make('testPassword' . uniqid());
        $testDirectoryRealPath = "/home/{$testUsername}/.htpasswd";

        $testHtPasswdRecords = [
            "{$testUsername}:{$testPassword}",
        ];

        $this->assertTrue(View::exists('server.samples.apache.htaccess.directory-privacy-htpasswd'));

        $testExpectedResult = view('server.samples.apache.htaccess.directory-privacy-htpasswd', [
            'htPasswdRecords' => $testHtPasswdRecords
        ])->render();

        $testBuilder = new HtpasswdBuild(false, $testDirectoryRealPath);
        $testResult = $testBuilder->getHtPasswdFileConfig($testHtPasswdRecords);

        $this->assertEquals($testExpectedResult, $testResult);
    }
}
