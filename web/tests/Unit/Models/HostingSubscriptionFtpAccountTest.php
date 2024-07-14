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
        $testFtpUsernamePrefix = 'testprefix' . rand(1000, 9999) . '_';

        $testFtpAccount = new HostingSubscriptionFtpAccount();
        $testFtpAccount->hosting_subscription_id = rand(1000, 9999);
        $testFtpAccount->ftp_username = $testFtpUsername;
        $testFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $testFtpAccount->ftp_password = time() . rand(1000, 9999);
        $testFtpAccount->ftp_path = '/home/test.com';
        $testFtpAccount->ftp_quota = 100;
        $testFtpAccount->save();

        $createdAccount = HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first();
        $this->assertNotNull($createdAccount);
        $this->assertEquals($testFtpUsername, $createdAccount->ftp_username);
        $this->assertEquals('/home/test.com', $createdAccount->ftp_path);
        $this->assertEquals(100, $createdAccount->ftp_quota);
        $this->assertEquals(0, $createdAccount->ftp_quota_type);

        $createdAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());

    }

    public function testHostingSubscriptionFtpAccountDeletion()
    {

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testprefix' . rand(1000, 9999) . '_';

        $testFtpAccount = new HostingSubscriptionFtpAccount();
        $testFtpAccount->hosting_subscription_id = rand(1000, 9999);
        $testFtpAccount->ftp_username = $testFtpUsername;
        $testFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $testFtpAccount->ftp_password = time() . rand(1000, 9999);
        $testFtpAccount->ftp_path = '/home/test.com';
        $testFtpAccount->ftp_quota = 100;
        $testFtpAccount->save();

        $testFtpAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());

    }

    public function testHostingSubscriptionFtpWithExistingAccount()
    {
        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testprefix' . rand(1000, 9999) . '_';
        $testHostingSubscriptionId = rand(1000, 9999);

        $existingAccount = new HostingSubscriptionFtpAccount();
        $existingAccount->hosting_subscription_id = $testHostingSubscriptionId;
        $existingAccount->ftp_username = $testFtpUsername;
        $existingAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $existingAccount->ftp_password = time() . rand(1000, 9999);
        $existingAccount->ftp_path = '/home/test.com';
        $existingAccount->ftp_quota = 100;
        $existingAccount->save();

        $createdAccount = HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first();
        $this->assertNotNull($createdAccount);

        $newFtpAccount = new HostingSubscriptionFtpAccount();
        $newFtpAccount->hosting_subscription_id = $testHostingSubscriptionId;
        $newFtpAccount->ftp_username = $testFtpUsername;
        $newFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $newFtpAccount->ftp_password = time() . rand(1000, 9999);
        $newFtpAccount->ftp_path = '/home/test.com';
        $newFtpAccount->quota = 100;

        $createResult = $newFtpAccount->createFtpAccount();

        $this->assertTrue($createResult['error']);
        $this->assertEquals('Ftp account already exists.', $createResult['message']);

        $existingAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());

    }

    public function testGetFtpQuotaTextAttributeWithQuota()
    {

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testprefix' . rand(1000, 9999) . '_';

        $ftpAccount = new HostingSubscriptionFtpAccount();
        $ftpAccount->ftp_username = $testFtpUsername;
        $ftpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $ftpAccount->ftp_quota = 100;

        $ftpAccount->save();
        $ftpQuotaText = $ftpAccount->getFtpQuotaTextAttribute();

        $this->assertEquals(100, $ftpQuotaText);

        $ftpAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());

    }

    public function testGetFtpQuotaAttributeWithQuotaType()
    {

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testprefix' . rand(1000, 9999) . '_';

        $ftpAccount = new HostingSubscriptionFtpAccount();
        $ftpAccount->ftp_username = $testFtpUsername;
        $ftpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $ftpAccount->ftp_quota_type = 'Unlimited';

        $ftpAccount->save();

        $ftpQuotaText = $ftpAccount->getFtpQuotaTextAttribute();

        $this->assertEquals('Unlimited', $ftpQuotaText);

        $ftpAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());

    }

    public function testGetFtpNameWithPrefixAttribute()
    {

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testprefix' . rand(1000, 9999) . '_';

        $ftpAccount = new HostingSubscriptionFtpAccount();
        $ftpAccount->ftp_username = $testFtpUsername;
        $ftpAccount->ftp_username_prefix = $testFtpUsernamePrefix;

        $ftpAccount->save();

        $ftpNameWithPrefix = $ftpAccount->getFtpNameWithPrefixAttribute();

        $expectedUsernameWithPrefix = $testFtpUsernamePrefix . $testFtpUsername;

        $this->assertEquals($expectedUsernameWithPrefix, $ftpNameWithPrefix);

        $ftpAccount->delete();
        $this->assertNull(HostingSubscriptionFtpAccount::where('ftp_username', $testFtpUsername)->first());
    }
}
