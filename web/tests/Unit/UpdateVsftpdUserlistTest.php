<?php

namespace tests\Unit;

use App\Jobs\UpdateVsftpdUserlist;
use App\Models\HostingSubscriptionFtpAccount;
use Tests\TestCase;

class UpdateVsftpdUserlistTest extends TestCase
{
    public function testUpdateVsftpdUserlist()
    {
        $subscription_data = [
            'hosting_subscription_id' => 1,
            'ftp_username' => 'testuser12',
            'ftp_password' => 'testpassword',
            'domain' => 'example.com',
        ];

        $ftpAccount = HostingSubscriptionFtpAccount::create($subscription_data);
        
        $filePath = '/etc/vsftpd.userlist';
        file_put_contents($filePath, '');

        $job = new UpdateVsftpdUserlist();
        $job->handle();

        $this->assertFileExists($filePath);
        $fileContent = file_get_contents($filePath);
        $this->assertNotEmpty($fileContent);

        $lines = explode(PHP_EOL, trim($fileContent));
        $lastLine = end($lines);

        $expectedViewRendered = "{$ftpAccount->ftp_username}:{$ftpAccount->domain}";

        $this->assertEquals($expectedViewRendered, trim($lastLine));

        $this->assertTrue(HostingSubscriptionFtpAccount::where('ftp_username', $ftpAccount->ftp_username)
            ->where('domain', $ftpAccount->domain)
            ->exists());

    }

}