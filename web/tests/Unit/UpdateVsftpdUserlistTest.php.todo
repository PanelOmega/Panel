<?php

namespace tests\Unit;

use App\Models\HostingSubscription\FtpAccount;
use Tests\TestCase;

class UpdateVsftpdUserlistTest extends TestCase
{
    public function testUpdateVsftpdUserlist()
    {

        // emptying the list to test if the job will fill it in through the view
        $filePath = '/etc/vsftpd/user_list';
        file_put_contents($filePath, '');

        $this->assertFileExists($filePath);

        $testFtpUsername = 'test' . rand(1000, 9999);
        $testFtpUsernamePrefix = 'testprefix' . rand(1000, 9999) . '_';

        $testFtpAccount = new FtpAccount();
        $testFtpAccount->hosting_subscription_id = rand(1000, 9999);
        $testFtpAccount->ftp_username = $testFtpUsername;
        $testFtpAccount->ftp_username_prefix = $testFtpUsernamePrefix;
        $testFtpAccount->ftp_password = time() . rand(1000, 9999);
        $testFtpAccount->ftp_path = '/home/test.com';
        $testFtpAccount->ftp_quota = 100;
        $testFtpAccount->save();

        $createdAccount = FtpAccount::where('ftp_username', $testFtpUsername)->first();
        $this->assertNotNull($createdAccount);


        $fileContent = file_get_contents($filePath);
        $this->assertNotEmpty($fileContent);


        $lines = explode(PHP_EOL, trim($fileContent));
        $lastLine = end($lines);

        $expectedViewRendered = "{$testFtpAccount->ftp_username_prefix}{$testFtpAccount->ftp_username}";

        $this->assertEquals($expectedViewRendered, trim($lastLine));

        $this->assertTrue(FtpAccount::where('ftp_username', $testFtpAccount->ftp_username)
            ->exists());

        $createdAccount->delete();
        $this->assertNull(FtpAccount::where('ftp_username', $testFtpUsername)->first());

    }

}
