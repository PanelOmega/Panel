<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\HtaccessBuildIndexes;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\Index;
use App\Models\HostingSubscription\IndexBrowse;
use App\Models\Traits\IndexTrait;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class IndexTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;
    use IndexTrait;

    public function testIndexCreate() {
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
        $this->assertNotEmpty($testPhpVersion);

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
        $testNewDirectory = "TestDirectory";
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }
        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = IndexBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testDirectoryType = 'folder';
        $testIndexTypes = $this->getIndexesIndexTypes();
        $testIndexTypes = array_filter($testIndexTypes, function ($key) {
            return in_array($key, ['No Indexing', 'Filename Only', 'Filename And Description']);
        }, ARRAY_FILTER_USE_KEY);

        $testIndexCreateObjects = [];
        foreach (array_keys($testIndexTypes) as $indexType) {
            $testIndexCreate = new Index();
            $testIndexCreate->hosting_subscription_id = $testHostingSubscription->id;
            $testIndexCreate->directory = $testNewDirectory;
            $testIndexCreate->directory_real_path = $testNewDirectory;
            $testIndexCreate->directory_type = $testDirectoryType;
            $testIndexCreate->index_type = $indexType;
            $testIndexCreate->save();

            $this->assertIsObject($testIndexCreate);
            $this->assertDatabaseHas('hosting_subscription_indices', [
                'hosting_subscription_id' => $testIndexCreate->hosting_subscription_id,
                'directory' => $testIndexCreate->directory,
                'directory_real_path' => $testIndexCreate->directory_real_path,
                'directory_type' => $testIndexCreate->directory_type,
                'index_type' => $testIndexCreate->index_type,
            ]);
            $testIndexCreateObjects[] = $testIndexCreate;
        }

        $testHtaccessBuildIndexes = new HtaccessBuildIndexes(false, $testHostingSubscription->id);
        foreach($testIndexCreateObjects as $obj) {
            $testIndexContent = $testHtaccessBuildIndexes->getIndexConfig($obj->index_type);
            if($obj->index_type == 'No Indexing') {
                $this->assertEquals([
                    'options' => 'Options -Indexes',
                    'indexOptions' => ''
                ], $testIndexContent);
            } elseif($obj->index_type == 'Filename Only') {
                $this->assertEquals([
                    'options' => 'Options +Indexes',
                    'indexOptions' => 'IndexOptions -HTMLTable -FancyIndexing'
                ], $testIndexContent);
            } elseif($obj->index_type == 'Filename And Description') {
                $this->assertEquals([
                    'options' => 'Options +Indexes',
                    'indexOptions' => 'IndexOptions +HTMLTable +FancyIndexing'
                ], $testIndexContent);
            }

            $testHtAccessView = $testHtaccessBuildIndexes->getHtAccessFileConfig($testIndexContent);
            $testIndexesConfigPath = $testDirectory . '/.htaccess';
            $this->assertTrue(is_file($testIndexesConfigPath));
            $testHtaccessBuildIndexes->updateSystemFile($testIndexesConfigPath, $testHtAccessView);
            $testSystemFileContent = file_get_contents($testIndexesConfigPath);
            $this->assertTrue(str_contains($testSystemFileContent, trim($testHtAccessView)));
        }
    }

    public function testIndexUpdate() {
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
        $this->assertNotEmpty($testPhpVersion);

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

        if (!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }
        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = IndexBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testDirectoryType = 'folder';
        $testIndexTypes = $this->getIndexesIndexTypes();
        $testIndexTypes = array_filter($testIndexTypes, function ($key) {
            return in_array($key, ['No Indexing', 'Filename Only', 'Filename And Description']);
        }, ARRAY_FILTER_USE_KEY);

        $testDirectoryType = 'folder';

        $testIndexCreate = new Index();
        $testIndexCreate->hosting_subscription_id = $testHostingSubscription->id;
        $testIndexCreate->directory = $testNewDirectory;
        $testIndexCreate->directory_real_path = $testNewDirectory;
        $testIndexCreate->directory_type = $testDirectoryType;
        $testIndexCreate->index_type = 'Inherit';
        $testIndexCreate->save();

        $this->assertIsObject($testIndexCreate);
        $this->assertDatabaseHas('hosting_subscription_indices', [
            'hosting_subscription_id' => $testIndexCreate->hosting_subscription_id,
            'directory' => $testIndexCreate->directory,
            'directory_real_path' => $testIndexCreate->directory_real_path,
            'directory_type' => $testIndexCreate->directory_type,
            'index_type' => $testIndexCreate->index_type,
        ]);

        $testIndexTypes = $this->getIndexesIndexTypes();
        $testIndexTypes = array_filter($testIndexTypes, function ($key) {
            return in_array($key, ['No Indexing', 'Filename Only', 'Filename And Description']);
        }, ARRAY_FILTER_USE_KEY);

        $testHtaccessBuildIndexes = new HtaccessBuildIndexes(false, $testHostingSubscription->id);

        foreach($testIndexTypes as $testIndexType) {
            $testIndexCreate->update([
                    'index_type' => $testIndexType
                ]);

            $testIndexContent = $testHtaccessBuildIndexes->getIndexConfig($testIndexType);

            if($testIndexType == 'No Indexing') {
                $this->assertEquals([
                    'options' => 'Options -Indexes',
                    'indexOptions' => ''
                ], $testIndexContent);
            } elseif($testIndexType == 'Filename Only') {
                $this->assertEquals([
                    'options' => 'Options +Indexes',
                    'indexOptions' => 'IndexOptions -HTMLTable -FancyIndexing'
                ], $testIndexContent);
            } elseif($testIndexType == 'Filename And Description') {
                $this->assertEquals([
                    'options' => 'Options +Indexes',
                    'indexOptions' => 'IndexOptions +HTMLTable +FancyIndexing'
                ], $testIndexContent);
            }

            $testHtaccessView = $testHtaccessBuildIndexes->getHtAccessFileConfig($testIndexContent);
            $testIndexesConfigPath = $testDirectory . '/.htaccess';
            $this->assertTrue(is_file($testIndexesConfigPath));
            $testHtaccessBuildIndexes->updateSystemFile($testIndexesConfigPath, $testHtaccessView);
            $testSystemFileContent = file_get_contents($testIndexesConfigPath);
            $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
        }
    }
}
