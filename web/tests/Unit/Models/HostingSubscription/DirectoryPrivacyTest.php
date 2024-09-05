<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtaccessBuildDirectoryPrivacy;
use App\Jobs\HtpasswdBuild;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\DirectoryPrivacy;
use App\Models\HostingSubscription\DirectoryPrivacyBrowse;
use App\Models\HostingSubscription\HtpasswdUser;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class DirectoryPrivacyTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;

    public function testCreateDirectoryPrivacy() {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
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

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = DirectoryPrivacyBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testUsername = 'testUsername' . rand(1000, 9999);
        $testPassword = 'testPassword' . rand(1000, 9999);
        $testLabel = 'testLabel' . rand(1000, 9999);

        $testCreateDirectoryPrivacy = new DirectoryPrivacy();
        $testCreateDirectoryPrivacy->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateDirectoryPrivacy->directory = $testDirectory;
        $testCreateDirectoryPrivacy->username = $testUsername;
        $testCreateDirectoryPrivacy->password = $testPassword;
        $testCreateDirectoryPrivacy->protected = true;
        $testCreateDirectoryPrivacy->label = $testLabel;
        $testCreateDirectoryPrivacy->path = $testNewDirectory;
        $testCreateDirectoryPrivacy->save();

        $this->assertIsObject($testCreateDirectoryPrivacy);
        $this->assertDatabaseHas('hosting_subscription_directory_privacies', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'directory' => $testCreateDirectoryPrivacy->directory,
            'username' => $testCreateDirectoryPrivacy->username,
            'password' => $testCreateDirectoryPrivacy->password,
            'protected' => $testCreateDirectoryPrivacy->protected,
            'label' => $testCreateDirectoryPrivacy->label,
            'path' => $testCreateDirectoryPrivacy->path
        ]);

        $testDirectoryPrivacyRealPath = "{$testBaseDir}/$testNewDirectory";
        $this->assertTrue(is_dir($testDirectoryPrivacyRealPath));
        $testHtpasswdDirectoryRealPath = "/home/{$testHostingSubscription->system_username}/.htpasswd";
        $this->assertTrue(is_file($testHtpasswdDirectoryRealPath));
        $testDirectoryPrivacyConfigPath = "$testDirectoryPrivacyRealPath/.htaccess";
        $this->assertTrue(is_file($testDirectoryPrivacyConfigPath));

        $testHtaccessBuildDirectoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $testDirectoryPrivacyRealPath, $testHostingSubscription->id);
        $testHtaccessBuildDirectoryPrivacyView = $testHtaccessBuildDirectoryPrivacy->getHtAccessFileConfig($testCreateDirectoryPrivacy->label, $testHtpasswdDirectoryRealPath, $testCreateDirectoryPrivacy->protected);
        $testHtaccessBuildDirectoryPrivacy->updateSystemFile($testDirectoryPrivacyConfigPath, $testHtaccessBuildDirectoryPrivacyView);
        $testSystemFileContent = file_get_contents($testDirectoryPrivacyConfigPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessBuildDirectoryPrivacyView)));

        $testCommand = "htpasswd -nb $testCreateDirectoryPrivacy->username $testCreateDirectoryPrivacy->password";
        $testHtpasswdCredentials = shell_exec($testCommand);
        $this->assertNotEmpty($testHtpasswdCredentials);

        $testHashedPassword = '';
        if ($testHtpasswdCredentials) {
            list($user, $hashedPasswd) = explode(':', trim($testHtpasswdCredentials), 2);
            $testHashedPassword = $hashedPasswd;
        }
        $testMd5Pattern = '/^\$apr1\$.{8}\$.{22}$/';
        $this->assertTrue(preg_match($testMd5Pattern, $testHashedPassword) === 1);

        $testCreatedHtpasswdUser = HtpasswdUser::where('directory', $testCreateDirectoryPrivacy->directory)
            ->where('username', $testCreateDirectoryPrivacy->username)->first();

        $this->assertIsObject($testCreatedHtpasswdUser);

        $testHtpasswdBuild = new HtpasswdBuild(false, $testHtpasswdDirectoryRealPath);
        $testGetHtpasswdRecords = $testHtpasswdBuild->getHtpasswdRecords([]);
        $this->assertNotEmpty($testGetHtpasswdRecords);
        $testHtpasswdView = $testHtpasswdBuild->getHtPasswdFileConfig($testGetHtpasswdRecords);
        $testHtpasswdBuild->updateSystemFile($testHtpasswdDirectoryRealPath, $testHtpasswdView);
        $testSystemFileContent = file_get_contents($testHtpasswdDirectoryRealPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtpasswdView)));
    }

    public function testUpdateDirectoryPrivacy() {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
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

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = DirectoryPrivacyBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testUsername = 'testUsername' . rand(1000, 9999);
        $testPassword = 'testPassword' . rand(1000, 9999);
        $testLabel = 'testLabel' . rand(1000, 9999);

        $testCreateDirectoryPrivacy = new DirectoryPrivacy();
        $testCreateDirectoryPrivacy->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateDirectoryPrivacy->directory = $testDirectory;
        $testCreateDirectoryPrivacy->username = $testUsername;
        $testCreateDirectoryPrivacy->password = $testPassword;
        $testCreateDirectoryPrivacy->protected = true;
        $testCreateDirectoryPrivacy->label = $testLabel;
        $testCreateDirectoryPrivacy->path = $testNewDirectory;
        $testCreateDirectoryPrivacy->save();

        $this->assertIsObject($testCreateDirectoryPrivacy);
        $this->assertDatabaseHas('hosting_subscription_directory_privacies', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'directory' => $testCreateDirectoryPrivacy->directory,
            'username' => $testCreateDirectoryPrivacy->username,
            'password' => $testCreateDirectoryPrivacy->password,
            'protected' => $testCreateDirectoryPrivacy->protected,
            'label' => $testCreateDirectoryPrivacy->label,
            'path' => $testCreateDirectoryPrivacy->path
        ]);

        $testCreateDirectoryPrivacy->update([
            'protected' => false
        ]);

        $this->assertFalse($testCreateDirectoryPrivacy->protected);

        $testDirectoryPrivacyRealPath = "{$testBaseDir}/$testNewDirectory";
        $this->assertTrue(is_dir($testDirectoryPrivacyRealPath));
        $testHtpasswdDirectoryRealPath = "/home/{$testHostingSubscription->system_username}/.htpasswd";
        $this->assertTrue(is_file($testHtpasswdDirectoryRealPath));
        $testDirectoryPrivacyConfigPath = "$testDirectoryPrivacyRealPath/.htaccess";
        $this->assertTrue(is_file($testDirectoryPrivacyConfigPath));

        $testHtaccessBuildDirectoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $testDirectoryPrivacyRealPath, $testHostingSubscription->id);
        $testHtaccessBuildDirectoryPrivacyView = $testHtaccessBuildDirectoryPrivacy->getHtAccessFileConfig($testCreateDirectoryPrivacy->label, $testHtpasswdDirectoryRealPath, $testCreateDirectoryPrivacy->protected);
        $this->assertEmpty($testHtaccessBuildDirectoryPrivacyView);
        $testHtaccessBuildDirectoryPrivacy->updateSystemFile($testDirectoryPrivacyConfigPath, $testHtaccessBuildDirectoryPrivacyView);
        $testSystemFileContent = file_get_contents($testDirectoryPrivacyConfigPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessBuildDirectoryPrivacyView)));

        $testCommand = "htpasswd -nb $testCreateDirectoryPrivacy->username $testCreateDirectoryPrivacy->password";
        $testHtpasswdCredentials = shell_exec($testCommand);
        $this->assertNotEmpty($testHtpasswdCredentials);

        $testHashedPassword = '';
        if ($testHtpasswdCredentials) {
            list($user, $hashedPasswd) = explode(':', trim($testHtpasswdCredentials), 2);
            $testHashedPassword = $hashedPasswd;
        }
        $testMd5Pattern = '/^\$apr1\$.{8}\$.{22}$/';
        $this->assertTrue(preg_match($testMd5Pattern, $testHashedPassword) === 1);

        $testCreatedHtpasswdUser = HtpasswdUser::where('directory', $testCreateDirectoryPrivacy->directory)
            ->where('username', $testCreateDirectoryPrivacy->username)->first();

        $this->assertIsObject($testCreatedHtpasswdUser);

        $testHtpasswdBuild = new HtpasswdBuild(false, $testHtpasswdDirectoryRealPath);
        $testGetHtpasswdRecords = $testHtpasswdBuild->getHtpasswdRecords([]);
        $this->assertNotEmpty($testGetHtpasswdRecords);
        $testHtpasswdView = $testHtpasswdBuild->getHtPasswdFileConfig($testGetHtpasswdRecords);
        $testHtpasswdBuild->updateSystemFile($testHtpasswdDirectoryRealPath, $testHtpasswdView);
        $testSystemFileContent = file_get_contents($testHtpasswdDirectoryRealPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtpasswdView)));
    }

    public function testDeleteDirectoryPrivacy() {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
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

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = DirectoryPrivacyBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testUsername = 'testUsername' . rand(1000, 9999);
        $testPassword = 'testPassword' . rand(1000, 9999);
        $testLabel = 'testLabel' . rand(1000, 9999);

        $testCreateDirectoryPrivacy = new DirectoryPrivacy();
        $testCreateDirectoryPrivacy->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateDirectoryPrivacy->directory = $testDirectory;
        $testCreateDirectoryPrivacy->username = $testUsername;
        $testCreateDirectoryPrivacy->password = $testPassword;
        $testCreateDirectoryPrivacy->protected = true;
        $testCreateDirectoryPrivacy->label = $testLabel;
        $testCreateDirectoryPrivacy->path = $testNewDirectory;
        $testCreateDirectoryPrivacy->save();

        $testCreateDirectoryPrivacyId = $testCreateDirectoryPrivacy->id;
        $this->assertIsObject($testCreateDirectoryPrivacy);
        $this->assertDatabaseHas('hosting_subscription_directory_privacies', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'directory' => $testCreateDirectoryPrivacy->directory,
            'username' => $testCreateDirectoryPrivacy->username,
            'password' => $testCreateDirectoryPrivacy->password,
            'protected' => $testCreateDirectoryPrivacy->protected,
            'label' => $testCreateDirectoryPrivacy->label,
            'path' => $testCreateDirectoryPrivacy->path
        ]);

        $testCommand = "htpasswd -nb $testCreateDirectoryPrivacy->username $testCreateDirectoryPrivacy->password";
        $testHtpasswdCredentials = shell_exec($testCommand);
        $this->assertNotEmpty($testHtpasswdCredentials);

        $testHashedPassword = '';
        if ($testHtpasswdCredentials) {
            list($user, $hashedPasswd) = explode(':', trim($testHtpasswdCredentials), 2);
            $testHashedPassword = $hashedPasswd;
        }
        $testMd5Pattern = '/^\$apr1\$.{8}\$.{22}$/';
        $this->assertTrue(preg_match($testMd5Pattern, $testHashedPassword) === 1);

        $testCreatedHtpasswdUser = HtpasswdUser::where('directory', $testCreateDirectoryPrivacy->directory)
            ->where('username', $testCreateDirectoryPrivacy->username)->first();

        $testDeletedPasswdUserId = $testCreatedHtpasswdUser->id;
        $testDeletedPasswdUserDirectory = $testCreatedHtpasswdUser->directory;
        $testDeletedHtPasswdUserUsername = $testCreatedHtpasswdUser->username;
        $testDeletedPasswdUserPassword = $testCreatedHtpasswdUser->password;

        $testDirectoryPrivacyRealPath = "{$testBaseDir}/$testNewDirectory";
        $this->assertTrue(is_dir($testDirectoryPrivacyRealPath));
        $testHtpasswdDirectoryRealPath = "/home/{$testHostingSubscription->system_username}/.htpasswd";
        $this->assertTrue(is_file($testHtpasswdDirectoryRealPath));
        $testDirectoryPrivacyConfigPath = "$testDirectoryPrivacyRealPath/.htaccess";
        $this->assertTrue(is_file($testDirectoryPrivacyConfigPath));

        $testCreateDirectoryPrivacy->delete();
        $this->assertDatabaseMissing('hosting_subscription_directory_privacies', [
            'hosting_subscription_id' => $testHostingSubscription->id,
            'id' => $testCreateDirectoryPrivacyId
        ]);

        $testFindDeletedDirectoryPrivacy = DirectoryPrivacy::where('id', $testCreateDirectoryPrivacyId)->first();
        $this->assertNull($testFindDeletedDirectoryPrivacy);

        $testHtaccessBuildDirectoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $testDirectoryPrivacyRealPath, $testHostingSubscription->id);
        $testLabel = $testFindDeletedDirectoryPrivacy->label ?? '';
        $testEnabled = $testFindDeletedDirectoryPrivacy->enabled ?? false;
        $testHtaccessBuildDirectoryPrivacyView = $testHtaccessBuildDirectoryPrivacy->getHtAccessFileConfig($testLabel, $testHtpasswdDirectoryRealPath, $testEnabled);
        $testHtaccessBuildDirectoryPrivacy->updateSystemFile($testDirectoryPrivacyConfigPath, $testHtaccessBuildDirectoryPrivacyView);
        $testSystemFileContent = file_get_contents($testDirectoryPrivacyConfigPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessBuildDirectoryPrivacyView)));


        $this->assertDatabaseMissing('hosting_subscription_htpasswd_users', [
            'id' => $testDeletedPasswdUserId,
            'directory' => $testDeletedPasswdUserDirectory,
            'username' => $testDeletedHtPasswdUserUsername,
            'password' => $testDeletedPasswdUserPassword,
        ]);

        $testHtpasswdBuild = new HtpasswdBuild(false, $testHtpasswdDirectoryRealPath);
        $testGetHtpasswdRecords = $testHtpasswdBuild->getHtpasswdRecords([]);
        $this->assertEmpty($testGetHtpasswdRecords);
        $testHtpasswdView = $testHtpasswdBuild->getHtPasswdFileConfig($testGetHtpasswdRecords);
        $testHtpasswdBuild->updateSystemFile($testHtpasswdDirectoryRealPath, $testHtpasswdView);
        $testSystemFileContent = file_get_contents($testHtpasswdDirectoryRealPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtpasswdView)));
    }
}
