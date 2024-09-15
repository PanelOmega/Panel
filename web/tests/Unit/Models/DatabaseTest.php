<?php

namespace tests\Unit\Models;

use App\Models\Customer;
use App\Models\Database;
use App\Models\HostingPlan;
use App\OmegaConfig;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use App\UniversalDatabaseExecutor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class DatabaseTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testCreateDatabase() {
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

        $testDatabaseName = 'testDB' . uniqid();
        $testCreateDatabase = new Database();
        $testCreateDatabase->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateDatabase->database_name = $testDatabaseName;
        $testCreateDatabase->is_remote_database_server = 0;
        $testCreateDatabase->remote_database_server_id = rand(1, 5);
        $testCreateDatabase->save();

        $this->assertIsObject($testCreateDatabase);

        $this->assertDatabaseHas('databases', [
            'id' => $testCreateDatabase->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);

        $this->assertEquals($testCreateDatabase->database_name_prefix, $testHostingSubscription->system_username . '_');

        $testUniversalDatabaseExecutor = new UniversalDatabaseExecutor(
            OmegaConfig::get('MYSQL_HOST', '127.0.0.1'),
            OmegaConfig::get('MYSQL_PORT', 3306),
            OmegaConfig::get('MYSQL_ROOT_USERNAME'),
            OmegaConfig::get('MYSQL_ROOT_PASSWORD'),
        );

        $this->assertInstanceOf(UniversalDatabaseExecutor::class, $testUniversalDatabaseExecutor);

        $testDatabaseExecName = strtolower($testCreateDatabase->database_name_prefix . Str::slug($testCreateDatabase->database_name, '_'));

        $testReflectExecutor = new \ReflectionClass($testUniversalDatabaseExecutor);
        $testReflectExecutorMethod = $testReflectExecutor->getMethod('_getDatabaseConnection');
        $testReflectExecutorMethod->setAccessible(true);

        $testConnection = $testReflectExecutorMethod->invoke($testUniversalDatabaseExecutor);

        $stmt = $testConnection->executeQuery('SHOW GRANTS FOR '. $testHostingSubscription->system_username);
        $testGrantsArr = $stmt->fetchAllAssociative();
        $this->assertNotEmpty($testGrantsArr);
        $testExpectedKey = "Grants for {$testHostingSubscription->system_username}@%";
        $testExpectedValue = "GRANT ALL PRIVILEGES ON `{$testDatabaseExecName}`.* TO `{$testHostingSubscription->system_username}`@`%`";
        $this->assertArrayHasKey($testExpectedKey, $testGrantsArr[1]);
        $this->assertTrue(in_array($testExpectedValue, $testGrantsArr[1]));
    }

    public function testDeleteDatabase() {
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

        $testDatabaseName = 'testDB' . uniqid();
        $testCreateDatabase = new Database();
        $testCreateDatabase->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateDatabase->database_name = $testDatabaseName;
        $testCreateDatabase->is_remote_database_server = 0;
        $testCreateDatabase->remote_database_server_id = rand(1, 5);
        $testCreateDatabase->save();

        $this->assertIsObject($testCreateDatabase);
        $this->assertTrue($testCreateDatabase->database_name_prefix === $testHostingSubscription->system_username . '_');

        $testCreateDatabase->delete();
        $this->assertDatabaseMissing('databases', [
            'id' => $testCreateDatabase->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);
    }
}
