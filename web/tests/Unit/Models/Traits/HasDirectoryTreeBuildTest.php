<?php

namespace tests\Unit\Models\Traits;

use _PHPStan_9815bbba4\Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\Traits\HasDirectoryTreeBuild;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RecursiveIteratorIterator;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class HasDirectoryTreeBuildTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use HasDirectoryTreeBuild;
    use DatabaseTransactions;

    public function testDirectoryTreeBuildWithNoDirectory()
    {
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

        $testBaseDir = "/home/{$testHostingSubscription->system_username}";
        shell_exec("rm -rf $testBaseDir");
        $this->assertFalse(is_dir($testBaseDir));

        $testResult = self::buildDirectoryTree();
        $this->assertEquals([], $testResult);
    }

    public function testDirectoryTreeBuildWithDirectory()
    {
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

        $testBaseDir = "/home/{$testHostingSubscription->system_username}";

        $testIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($testBaseDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $this->assertNotEmpty($testIterator);
        $this->assertEquals($testBaseDir . '/public_html', $testIterator->key());
        $this->assertTrue($testIterator->callHasChildren());
        $this->assertTrue($testIterator->valid());

        $testDirectoryTree = [];

        foreach ($testIterator as $path => $fileInfo) {
            if ($fileInfo->isDir()) {
                $relativePath = str_replace($testBaseDir, '', $path);
                $relativePath = trim($relativePath, '/');
                $pathParts = explode('/', $relativePath);

                if ($pathParts[0] !== 'public_html') {
                    continue;
                }

                $currentLevel = &$testDirectoryTree;
                foreach ($pathParts as $part) {
                    if (!isset($currentLevel[$part])) {
                        $currentLevel[$part] = [
                            'name' => $part,
                            'value' => $relativePath,
                            'children' => []
                        ];
                    }
                    $currentLevel = &$currentLevel[$part]['children'];
                }
            }
        }

        $testExpected = self::_formatTree($testDirectoryTree);
        $testResult = self::buildDirectoryTree();
        $this->assertEquals($testExpected, $testResult);
    }

//    public function testDirectoryTreeBuildFormatTree()
//    {
//        $testInput = [
//            'test_dir' => [
//                'name' => 'test_dir',
//                'value' => 'test_dir',
//                'children' => [
//                    'test_subdir' => [
//                        'name' => 'test_subdir',
//                        'value' => 'test_subdir',
//                        'children' => []
//                    ]
//                ]
//            ]
//        ];
//
//        $testOutput = [
//            'test_dir' => [
//                'name' => 'test_dir',
//                'value' => 'test_dir',
//                'children' => [
//                    'test_subdir' => [
//                        'name' => 'test_subdir',
//                        'value' => 'test_subdir',
//                        'children' => []
//                    ]
//                ]
//            ]
//        ];
//
//        $testResult = self::_formatTree($testInput);
//        $this->assertEquals($testOutput, $testResult);
//    }
}
