<?php

namespace tests\Unit\Models\HostingSubscription;

use App\Jobs\UpdateVsftpdUserlist;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\FtpAccount;
use App\Server\Helpers\LinuxUser;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class FtpAccountTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;

    public function testCreateFtpAccount() {
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

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsername = Str::slug($testFtpUsername, '_');
        $testFtpUsernamePrefix = 'testPrefix' . rand(1000, 9999);
        $testFtpPassword = 'test' . rand(1000, 9999);

        $testSystemUsername = $testHostingSubscription->system_username;
        $testBaseDir = "/home/{$testSystemUsername}/public_html";

        $testNewDirectory = 'TestDirectory';
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $testCreateFtpAccount = new FtpAccount();
        $testCreateFtpAccount->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateFtpAccount->domain = $testHostingSubscription->domain;
        $testCreateFtpAccount->ftp_username = $testFtpUsername;
        $testCreateFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $testCreateFtpAccount->ftp_password = $testFtpPassword;
        $testCreateFtpAccount->ftp_path = $testNewDirectory;
        $testCreateFtpAccount->ftp_quota = true;
        $testCreateFtpAccount->ftp_quota_type = 'unlimited';
        $testCreateFtpAccount->save();

        $this->assertIsObject($testCreateFtpAccount);
        $this->assertDatabaseHas('hosting_subscription_ftp_accounts', [
            'id' => $testCreateFtpAccount->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testGetAccountByName = new \ReflectionMethod(FtpAccount::class, '_getFtpAccountByUsername');
        $testGetAccountByName->setAccessible(true);
        $testResult = $testGetAccountByName->invoke($testCreateFtpAccount, $testCreateFtpAccount->ftp_username, $testCreateFtpAccount->ftp_username);
        $this->assertIsObject($testResult);
        $this->assertEquals($testCreateFtpAccount->id, $testResult->id);


        $testLinuxUsername = 'test' . rand(1000, 9999);
        $testLinuxUsername = Str::slug($testLinuxUsername, '_');
        $testLinuxPassword = 'test' . rand(1000, 9999);
        $testLinuxUsernamePrefix = $testSystemUsername . '_';
        $testLinuxUserBaseDir = "/home/{$testSystemUsername}";

        $testCreateLinuxUsernameWithPrefix = $testLinuxUsernamePrefix . $testLinuxUsername;
        $testCreateLinuxUser = LinuxUser::createUser(
            $testCreateLinuxUsernameWithPrefix,
            $testLinuxPassword,
            $testHostingSubscription->customer->email,
            [
                'homeDir' => $testLinuxUserBaseDir
            ]
        );

        $this->assertNotEmpty($testCreateLinuxUser);
        $this->assertArrayHasKey('success', $testCreateLinuxUser);

        $testCommands = [
            "sudo usermod -d $testLinuxUserBaseDir $testCreateLinuxUsernameWithPrefix",
            "sudo usermod -a -G $testSystemUsername $testCreateLinuxUsernameWithPrefix",
            "sudo usermod -a -G $testSystemUsername $testSystemUsername",
            "sudo chgrp -R $testSystemUsername $testLinuxUserBaseDir",
            "sudo chmod -R 770 $testLinuxUserBaseDir",
        ];

        foreach($testCommands as $command) {
            $testOutput = shell_exec($command . ' 2>&1');
            $this->assertTrue(
                $testOutput === null || trim($testOutput) === 'usermod: no changes'
            );
        }
    }

    public function testDeleteFtpAccount() {
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

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testPrefix' . rand(1000, 9999);
        $testFtpPassword = 'test' . rand(1000, 9999);

        $testSystemUsername = $testHostingSubscription->system_username;
        $testBaseDir = "/home/{$testSystemUsername}/public_html";
        $testNewDirectory = 'TestDirectory';
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $testCreateFtpAccount = new FtpAccount();
        $testCreateFtpAccount->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateFtpAccount->domain = $testHostingSubscription->domain;
        $testCreateFtpAccount->ftp_username = $testFtpUsername;
        $testCreateFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $testCreateFtpAccount->ftp_password = $testFtpPassword;
        $testCreateFtpAccount->ftp_path = $testNewDirectory;
        $testCreateFtpAccount->ftp_quota = true;
        $testCreateFtpAccount->ftp_quota_type = 'unlimited';
        $testCreateFtpAccount->save();

        $this->assertIsObject($testCreateFtpAccount);
        $this->assertDatabaseHas('hosting_subscription_ftp_accounts', [
            'id' => $testCreateFtpAccount->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testDeleteUsername = strtolower($testFtpUsernamePrefix . $testFtpUsername);

        $testCommands = [
            "userdel $testDeleteUsername",
            "id $testDeleteUsername"
        ];

        foreach($testCommands as $command) {
            $this->assertTrue(shell_exec($command) === null);
        }
    }

    public function testUpdateVsftpdUserlistCreate() {
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

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testPrefix' . rand(1000, 9999);
        $testFtpPassword = 'test' . rand(1000, 9999);

        $testSystemUsername = $testHostingSubscription->system_username;
        $testBaseDir = "/home/{$testSystemUsername}/public_html";
        $testNewDirectory = 'TestDirectory';
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $testCreateFtpAccount = new FtpAccount();
        $testCreateFtpAccount->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateFtpAccount->domain = $testHostingSubscription->domain;
        $testCreateFtpAccount->ftp_username = $testFtpUsername;
        $testCreateFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $testCreateFtpAccount->ftp_password = $testFtpPassword;
        $testCreateFtpAccount->ftp_path = $testNewDirectory;
        $testCreateFtpAccount->ftp_quota = true;
        $testCreateFtpAccount->ftp_quota_type = 'unlimited';
        $testCreateFtpAccount->save();

        $this->assertIsObject($testCreateFtpAccount);
        $this->assertDatabaseHas('hosting_subscription_ftp_accounts', [
            'id' => $testCreateFtpAccount->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testVsftpdSystemFilePath = '/etc/vsftpd/user_list';
        $this->assertFileExists($testVsftpdSystemFilePath);

        $testUpdateFtpUserList = new UpdateVsftpdUserlist();
        $testFtpAccounts = FtpAccount::all();
        $testVsftpdFileView = $testUpdateFtpUserList->getVsftpdFileConfig($testFtpAccounts);
        $testUpdateFtpUserList->updateSystemFile($testVsftpdSystemFilePath, $testVsftpdFileView);
        $testSystemFileContent = file_get_contents($testVsftpdSystemFilePath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testVsftpdFileView)));
    }

    public function testUpdateVsftpdUserlistDelete() {
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

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testPrefix' . rand(1000, 9999);
        $testFtpPassword = 'test' . rand(1000, 9999);

        $testSystemUsername = $testHostingSubscription->system_username;
        $testBaseDir = "/home/{$testSystemUsername}/public_html";
        $testNewDirectory = 'TestDirectory';
        $testDirectory = "{$testBaseDir}/{$testNewDirectory}";

        if(!is_dir($testDirectory)) {
            mkdir($testDirectory);
        }

        $testCreateFtpAccount = new FtpAccount();
        $testCreateFtpAccount->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateFtpAccount->domain = $testHostingSubscription->domain;
        $testCreateFtpAccount->ftp_username = $testFtpUsername;
        $testCreateFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $testCreateFtpAccount->ftp_password = $testFtpPassword;
        $testCreateFtpAccount->ftp_path = $testNewDirectory;
        $testCreateFtpAccount->ftp_quota = true;
        $testCreateFtpAccount->ftp_quota_type = 'unlimited';
        $testCreateFtpAccount->save();
        $testCreateFtpAccountId = $testCreateFtpAccount->id;

        $testCreateFtpAccount->delete();
        $this->assertDatabaseMissing('hosting_subscription_ftp_accounts', [
            'id' => $testCreateFtpAccountId,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testVsftpdSystemFilePath = '/etc/vsftpd/user_list';
        $this->assertFileExists($testVsftpdSystemFilePath);
        $testUpdateFtpUserList = new UpdateVsftpdUserlist();
        $testFtpAccounts = FtpAccount::all();
        $this->assertEmpty($testFtpAccounts);
        $testVsftpdFileView = $testUpdateFtpUserList->getVsftpdFileConfig($testFtpAccounts);
        $testUpdateFtpUserList->updateSystemFile($testVsftpdSystemFilePath, $testVsftpdFileView);
        $testSystemFileContent = file_get_contents($testVsftpdSystemFilePath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testVsftpdFileView)));
    }
}
