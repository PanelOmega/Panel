<?php

namespace tests\Unit\Models;

use Tests\TestCase;
use App\Models\HostingSubscriptionFtpAccount;
use App\Server\Helpers\FtpAccount;
use App\Jobs\UpdateVsftpdUserlist;

class HostingSubscriptionFtpAccountTest extends TestCase
{
    public function testHostingSubscriptionFtpAccountCreation()
    {

        $testFtpUsername = 'test' . rand(1000, 9999);

        $testFtpAccount = new HostingSubscriptionFtpAccount();
        $testFtpAccount->hosting_subscription_id = rand(1000, 9999);
        $testFtpAccount->ftp_username = $testFtpUsername;
        $testFtpAccount->ftp_password = time() . rand(1000, 9999);
        $testFtpAccount->ftp_path = '/home/test.com';
        $testFtpAccount->ftp_quota = 100;
        $testFtpAccount->ftp_quota_unlimited = false;
        $testFtpAccount->save();

        $createdAccount = HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first();
        $this->assertNotNull($createdAccount);
        $this->assertEquals($testFtpUsername, $createdAccount->ftp_username);
        $this->assertEquals('/home/test.com', $createdAccount->ftp_path);
        $this->assertEquals(100, $createdAccount->ftp_quota);
        $this->assertEquals(0, $createdAccount->ftp_quota_unlimited);

        $createdAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());

        $this->assertNotNull(FtpAccount::getFtpAccount($testFtpUsername));
    }

    public function testHostingSubscriptionFtpAccountDeletion() {

        $testFtpUsername = 'test' . rand(1000, 9999);

        $testFtpAccount = new HostingSubscriptionFtpAccount();
        $testFtpAccount->hosting_subscription_id = rand(1000, 9999);
        $testFtpAccount->ftp_username = $testFtpUsername;
        $testFtpAccount->ftp_password = time() . rand(1000, 9999);
        $testFtpAccount->ftp_path = '/home/test.com';
        $testFtpAccount->ftp_quota = 100;
        $testFtpAccount->ftp_quota_unlimited = false;
        $testFtpAccount->save();

        $testFtpAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());

        $ftpAccountDeletionResult = FtpAccount::deleteFtpAccount($testFtpUsername);
        $this->assertArrayHasKey('success', $ftpAccountDeletionResult);

        $this->assertTrue(UpdateVsftpdUserlist::$handled);
    }
}
