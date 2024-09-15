<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtpasswdBuild;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\HtpasswdUser;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class HtpasswdUserTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;

    public function testCreateHtpasswdUser() {
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

        $testSystemUsername = $testHostingSubscription->system_username;
        $testBaseDir = "/home/{$testSystemUsername}/public_html";
        $testNewDirectory = 'TestDirectory';
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";
        $testHtpasswdDirectoryRealPath = "/home/$testSystemUsername/.htpasswd";

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testUsername = 'testUsername' . uniqid();
        $testPassword = 'testPassword' . uniqid();

        $testCreateHtpasswdUser = new HtpasswdUser();
        $testCreateHtpasswdUser->directory = $testDirectory;
        $testCreateHtpasswdUser->username = $testUsername;

        $testCommand = "htpasswd -nb $testUsername $testPassword";
        $testHtpasswdCredentials = shell_exec($testCommand);
        $this->assertNotEmpty($testHtpasswdCredentials);

        $testHashedPassword = '';
        if ($testHtpasswdCredentials) {
            list($user, $hashedPasswd) = explode(':', trim($testHtpasswdCredentials), 2);
            $testHashedPassword = $hashedPasswd;
        }
        $testMd5Pattern = '/^\$apr1\$.{8}\$.{22}$/';
        $this->assertTrue(preg_match($testMd5Pattern, $testHashedPassword) === 1);

        $testCreateHtpasswdUser->password = $testHashedPassword;
        $testCreateHtpasswdUser->save();

        $this->assertIsObject($testCreateHtpasswdUser);
        $this->assertDatabaseHas('hosting_subscription_htpasswd_users', [
            'id' => $testCreateHtpasswdUser->id,
            'username' => $testCreateHtpasswdUser->username,
            'password' => $testCreateHtpasswdUser->password
        ]);

        $this->assertTrue(file_exists($testHtpasswdDirectoryRealPath));
        $testHtpasswdBuild = new HtpasswdBuild(false, $testHtpasswdDirectoryRealPath);
        $testGetHtpasswdRecords = $testHtpasswdBuild->getHtpasswdRecords([]);
        $this->assertNotEmpty($testGetHtpasswdRecords);
        $testHtpasswdView = $testHtpasswdBuild->getHtPasswdFileConfig($testGetHtpasswdRecords);
        $testSystemFileContent = file_get_contents($testHtpasswdDirectoryRealPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtpasswdView)));
    }

    public function testDeleteHtpasswdUser() {
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

        $testSystemUsername = $testHostingSubscription->system_username;
        $testBaseDir = "/home/{$testSystemUsername}/public_html";
        $testNewDirectory = 'TestDirectory';
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";
        $testHtpasswdDirectoryRealPath = "/home/$testSystemUsername/.htpasswd";

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testUsername = 'testUsername' . uniqid();
        $testPassword = 'testPassword' . uniqid();

        $testCreateHtpasswdUser = new HtpasswdUser();
        $testCreateHtpasswdUser->directory = $testDirectory;
        $testCreateHtpasswdUser->username = $testUsername;

        $testCommand = "htpasswd -nb $testUsername $testPassword";
        $testHtpasswdCredentials = shell_exec($testCommand);
        $this->assertNotEmpty($testHtpasswdCredentials);

        $testHashedPassword = '';
        if ($testHtpasswdCredentials) {
            list($user, $hashedPasswd) = explode(':', trim($testHtpasswdCredentials), 2);
            $testHashedPassword = $hashedPasswd;
        }
        $testMd5Pattern = '/^\$apr1\$.{8}\$.{22}$/';
        $this->assertTrue(preg_match($testMd5Pattern, $testHashedPassword) === 1);

        $testCreateHtpasswdUser->password = $testHashedPassword;
        $testCreateHtpasswdUser->save();

        $this->assertIsObject($testCreateHtpasswdUser);

        $command = "htpasswd -D {$testHtpasswdDirectoryRealPath} {$testCreateHtpasswdUser->username}";
        $this->assertTrue(shell_exec($command) ===  null);

        $testCreateHtpasswdUser->delete();
        $this->assertDatabaseMissing('hosting_subscription_htpasswd_users', [
            'id' => $testCreateHtpasswdUser->id
        ]);

        $this->assertTrue(file_exists($testHtpasswdDirectoryRealPath));
        $testHtpasswdBuild = new HtpasswdBuild(false, $testHtpasswdDirectoryRealPath);
        $testGetHtpasswdRecords = $testHtpasswdBuild->getHtpasswdRecords([]);
        $this->assertEmpty($testGetHtpasswdRecords);
        $testHtpasswdView = $testHtpasswdBuild->getHtPasswdFileConfig($testGetHtpasswdRecords);
        $testSystemFileContent = file_get_contents($testHtpasswdDirectoryRealPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtpasswdView)));
    }
}
