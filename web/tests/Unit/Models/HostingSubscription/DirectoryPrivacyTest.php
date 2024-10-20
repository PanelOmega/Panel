<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtaccessBuildDirectoryPrivacy;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\DirectoryPrivacy;
use App\Models\HostingSubscription\DirectoryPrivacyBrowse;
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

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = DirectoryPrivacyBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testUsername = 'testUsername' . uniqid();
        $testPassword = 'testPassword' . uniqid();
        $testLabel = 'testLabel' . uniqid();

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
            'id' => $testCreateDirectoryPrivacy->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testDirectoryPrivacyRealPath = "{$testBaseDir}/$testNewDirectory";
        $this->assertTrue(is_dir($testDirectoryPrivacyRealPath));
        $testHtpasswdDirectoryRealPath = "/home/{$testSystemUsername}/.htpasswd";
        $this->assertTrue(file_exists($testHtpasswdDirectoryRealPath));
        $testDirectoryPrivacyConfigPath = "$testDirectoryPrivacyRealPath/.htaccess";
        $this->assertTrue(file_exists($testDirectoryPrivacyConfigPath));

        $testDirectoryPrivacyData = [
            'protected' => $testCreateDirectoryPrivacy->protected,
            'label' => $testCreateDirectoryPrivacy->label,
        ];
        $testHtaccessBuildDirectoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $testDirectoryPrivacyRealPath, $testHostingSubscription->id, $testDirectoryPrivacyData);
        $testHtaccessBuildDirectoryPrivacyView = $testHtaccessBuildDirectoryPrivacy->getHtAccessFileConfig($testCreateDirectoryPrivacy->label, $testHtpasswdDirectoryRealPath, $testCreateDirectoryPrivacy->protected);
        $testSystemFileContent = file_get_contents($testDirectoryPrivacyConfigPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessBuildDirectoryPrivacyView)));
    }

    public function testUpdateDirectoryPrivacy() {
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

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = DirectoryPrivacyBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testUsername = 'testUsername' . uniqid();
        $testPassword = 'testPassword' . uniqid();
        $testLabel = 'testLabel' . uniqid();

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
            'id' => $testCreateDirectoryPrivacy->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testCreateDirectoryPrivacy->update([
            'protected' => false
        ]);

        $this->assertFalse($testCreateDirectoryPrivacy->protected);

        $testDirectoryPrivacyRealPath = "{$testBaseDir}/$testNewDirectory";
        $this->assertTrue(is_dir($testDirectoryPrivacyRealPath));
        $testHtpasswdDirectoryRealPath = "/home/{$testHostingSubscription->system_username}/.htpasswd";
        $this->assertTrue(file_exists($testHtpasswdDirectoryRealPath));
        $testDirectoryPrivacyConfigPath = "$testDirectoryPrivacyRealPath/.htaccess";
        $this->assertTrue(file_exists($testDirectoryPrivacyConfigPath));

        $testDirectoryPrivacyData = [
            'protected' => $testCreateDirectoryPrivacy->protected,
            'label' => $testCreateDirectoryPrivacy->label,
        ];
        $testHtaccessBuildDirectoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $testDirectoryPrivacyRealPath, $testHostingSubscription->id, $testDirectoryPrivacyData);
        $testHtaccessBuildDirectoryPrivacyView = $testHtaccessBuildDirectoryPrivacy->getHtAccessFileConfig($testCreateDirectoryPrivacy->label, $testHtpasswdDirectoryRealPath, $testCreateDirectoryPrivacy->protected);
        $this->assertEmpty($testHtaccessBuildDirectoryPrivacyView);
        $testSystemFileContent = file_get_contents($testDirectoryPrivacyConfigPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessBuildDirectoryPrivacyView)));
    }

    public function testDeleteDirectoryPrivacy() {
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

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = DirectoryPrivacyBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testUsername = 'testUsername' . uniqid();
        $testPassword = 'testPassword' . uniqid();
        $testLabel = 'testLabel' . uniqid();

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

        $testDirectoryPrivacyRealPath = "{$testBaseDir}/$testNewDirectory";
        $this->assertTrue(is_dir($testDirectoryPrivacyRealPath));
        $testHtpasswdDirectoryRealPath = "/home/{$testHostingSubscription->system_username}/.htpasswd";
        $this->assertTrue(file_exists($testHtpasswdDirectoryRealPath));
        $testDirectoryPrivacyConfigPath = "$testDirectoryPrivacyRealPath/.htaccess";
        $this->assertTrue(file_exists($testDirectoryPrivacyConfigPath));

        $testCreateDirectoryPrivacy->delete();
        $this->assertDatabaseMissing('hosting_subscription_directory_privacies', [
            'id' => $testCreateDirectoryPrivacy->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testFindDeletedDirectoryPrivacy = DirectoryPrivacy::where('id', $testCreateDirectoryPrivacy->id)->first();
        $this->assertNull($testFindDeletedDirectoryPrivacy);

        $testHtaccessBuildDirectoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $testDirectoryPrivacyRealPath, $testHostingSubscription->id, []);
        $testLabel = $testFindDeletedDirectoryPrivacy->label ?? '';
        $testEnabled = $testFindDeletedDirectoryPrivacy->enabled ?? false;
        $testHtaccessBuildDirectoryPrivacyView = $testHtaccessBuildDirectoryPrivacy->getHtAccessFileConfig($testLabel, $testHtpasswdDirectoryRealPath, $testEnabled);
        $testSystemFileContent = file_get_contents($testDirectoryPrivacyConfigPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessBuildDirectoryPrivacyView)));
    }
}
