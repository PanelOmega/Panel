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

    public function testCreateIndex()
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

        $testSystemUsername = $testHostingSubscription->system_username;
        $testBaseDir = "/home/{$testSystemUsername}/public_html";
        $testNewDirectory = "TestDirectory";
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";

        if (!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }
        $this->assertTrue($testDirectory && is_dir($testDirectory));

        $testQuery = IndexBrowse::queryForDiskAndPath($testBaseDir, '');
        $testIfDirectoryExists = $testQuery->where('directory', $testNewDirectory)->first();
        $this->assertNotEmpty($testIfDirectoryExists);

        $testDirectoryType = 'folder';

        $testIndexCreate = new Index();
        $testIndexCreate->hosting_subscription_id = $testHostingSubscription->id;
        $testIndexCreate->directory = $testNewDirectory;
        $testIndexCreate->directory_real_path = $testNewDirectory;
        $testIndexCreate->directory_type = $testDirectoryType;
        $testIndexCreate->index_type = 'Filename And Description';
        $testIndexCreate->save();

        $this->assertIsObject($testIndexCreate);
        $this->assertDatabaseHas('hosting_subscription_indices', [
            'id' => $testIndexCreate->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testHtaccessBuildIndexes = new HtaccessBuildIndexes(false, $testHostingSubscription->id);
        $testIndexContent = $testHtaccessBuildIndexes->getIndexConfig($testIndexCreate->index_type);

        $this->assertEquals([
            'options' => 'Options +Indexes',
            'indexOptions' => 'IndexOptions +HTMLTable +FancyIndexing'
        ], $testIndexContent);

        $testHtAccessView = $testHtaccessBuildIndexes->getHtAccessFileConfig($testIndexContent);
        $testIndexesConfigPath = $testDirectory . '/.htaccess';
        $this->assertTrue(file_exists($testIndexesConfigPath));
        $testSystemFileContent = file_get_contents($testIndexesConfigPath);
        $this->assertTrue(str_contains($testSystemFileContent, trim($testHtAccessView)));
    }

    public function testUpdateIndex()
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

        $testIndexCreate = new Index();
        $testIndexCreate->hosting_subscription_id = $testHostingSubscription->id;
        $testIndexCreate->directory = $testNewDirectory;
        $testIndexCreate->directory_real_path = $testNewDirectory;
        $testIndexCreate->directory_type = $testDirectoryType;
        $testIndexCreate->index_type = 'Inherit';
        $testIndexCreate->save();

        $this->assertIsObject($testIndexCreate);
        $this->assertDatabaseHas('hosting_subscription_indices', [
            'id' => $testIndexCreate->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);

        $testHtaccessBuildIndexes = new HtaccessBuildIndexes(false, $testHostingSubscription->id);

        $testIndexTypeUpdate = 'Filename Only';
        $testIndexCreate->update([
            'index_type' => $testIndexTypeUpdate
        ]);

        $testIndexContent = $testHtaccessBuildIndexes->getIndexConfig($testIndexCreate->index_type);

        $this->assertEquals([
            'options' => 'Options +Indexes',
            'indexOptions' => 'IndexOptions -HTMLTable -FancyIndexing'
        ], $testIndexContent);

        $testHtaccessView = $testHtaccessBuildIndexes->getHtAccessFileConfig($testIndexContent);
        $testIndexesConfigPath = $testDirectory . '/.htaccess';
        $this->assertTrue(file_exists($testIndexesConfigPath));
        $testSystemFileContent = file_get_contents($testIndexesConfigPath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
    }
}
