<?php

namespace tests\Unit\Server\Helpers;

use Tests\TestCase;
use App\Models\HostingSubscriptionFtpAccount;
use App\Server\Helpers\FtpAccount;

class FtpAccountTest extends TestCase
{
    public function testCreateFtpAccount(): void
    {
        $subscription_data = [
            'hosting_subscription_id' => 1,
            'ftp_username' => 'testuser' . rand(1, 1000),
            'ftp_password' => 'testpassword',
        ];

        $result = FtpAccount::createFtpAccount($subscription_data);

        $this->assertArrayHasKey('success', $result);

        $ftpAccount = HostingSubscriptionFtpAccount::where('ftp_username', $subscription_data['ftp_username'])->first();
        $this->assertNotNull($ftpAccount);
        $this->assertEquals($subscription_data['hosting_subscription_id'], $ftpAccount->hosting_subscription_id);
        $this->assertEquals($subscription_data['domain'], $ftpAccount->domain);

        $this->assertTrue(is_dir('/home/' . $subscription_data['ftp_username']));
        $this->assertTrue(is_dir('/home/' . $subscription_data['ftp_username'] . '/ftp_rootpath'));

    }

    public function testCreateExistingFtpAccount(): void
    {

        $existing_account = [
            'hosting_subscription_id' => 1,
            'ftp_username' => 'existinguser',
            'ftp_password' => 'testpassword',
            'domain' => 'example.com',
        ];

        HostingSubscriptionFtpAccount::create($existing_account);
        $result = FtpAccount::createFtpAccount($existing_account);

        $this->assertTrue($result['error']);
        $this->assertSame('Ftp account already exists.', $result['message']);
    }

    public function testDeleteFtpAccount(): void
    {

        $subscription_data = [
            'hosting_subscription_id' => 1,
            'ftp_username' => 'deletetestuser' . rand(1, 1000),
            'ftp_password' => 'testpassword',
            'domain' => 'example.com',
        ];

        FtpAccount::createFtpAccount($subscription_data);

        $result = FtpAccount::deleteFtpAccount($subscription_data['ftp_username']);

        $this->assertArrayHasKey('success', $result);

        $ftpAccount = HostingSubscriptionFtpAccount::where('ftp_username', $subscription_data['ftp_username'])->first();
        $this->assertNull($ftpAccount);

        $username = $subscription_data['ftp_username'];
        $output = shell_exec("id $username");

        $this->assertTrue($output === null);

        $userListPath = '/etc/vsftpd.userlist';
        $command = "cat {$userListPath}";
        exec($command, $userList, $returnCode);

        $userExistsInList = array_filter($userList, function($user) use ($subscription_data) {
            return str_starts_with($user, $subscription_data['ftp_username'] . ':');
        });
        $this->assertCount(0, $userExistsInList);

    }
}
