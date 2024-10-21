<?php

namespace tests\Unit\Jobs;

use App\Jobs\UpdateVsftpdUserlist;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\FtpAccount;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class UpdateVsftpdUserlistTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testHandle()
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

        $filePath = '/etc/vsftpd/user_list';
        file_put_contents($filePath, '');

        $this->assertFileExists($filePath);

        $testFtpUsername = 'test' . uniqid();
        $testFtpUsername = Str::slug($testFtpUsername, '_');
        $testFtpUsernamePrefix = 'testPrefix' . uniqid();
        $testFtpPassword = 'test' . uniqid();

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

        $createdAccount = FtpAccount::where('ftp_username', $testFtpUsername)->first();
        $this->assertNotNull($createdAccount);

        $fileContent = file_get_contents($filePath);
        $this->assertNotEmpty($fileContent);

        $lines = explode(PHP_EOL, trim($fileContent));
        $lastLine = end($lines);

        $expectedViewRendered = "{$testCreateFtpAccount->ftp_username_prefix}{$testCreateFtpAccount->ftp_username}";

        $this->assertEquals($expectedViewRendered, trim($lastLine));
    }

    public function testGetVsftpdFileConfig() {
        $testFtpUserNamePrefix = 'testPrefix' . uniqid();
        $testFtpUserName = 'testUsername' . uniqid();

        $testFtpAccounts = [
            (object) [
                'ftp_username_prefix' => $testFtpUserNamePrefix,
                'ftp_username' => $testFtpUserName,
            ]
        ];

        $this->assertTrue(View::exists('server.samples.vsftpd.vsftpd-userlist-conf'));

        $testExpectedResult = $testFtpUserNamePrefix . $testFtpUserName;

        $testResult = view('server.samples.vsftpd.vsftpd-userlist-conf', [
            'ftpAccounts' => $testFtpAccounts
        ])->render();

        $this->assertNotEmpty($testResult);

        $this->assertEquals($testExpectedResult, trim($testResult));
    }
}
