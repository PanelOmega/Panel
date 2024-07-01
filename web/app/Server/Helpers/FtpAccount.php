<?php

namespace App\Server\Helpers;

use App\Models\HostingSubscriptionFtpAccount;
use App\Jobs\UpdateVsftpdUserlist;

class FtpAccount {

    /**
     * @param array $subscription_data
     * @return string
     */
     public static function createFtpAccount(array $subscription_data) {


        $checkFtpUser = self::getFtpAccount($subscription_data['ftp_username'], $subscription_data['domain']);

        if (!empty($checkFtpUser)) {
            return [
                'error' => true,
                'message' => 'Ftp account already exists.'
            ];
        }

        $ftpUsername = $subscription_data['ftp_username'];
        $ftpPassword = $subscription_data['ftp_password'];
        $ftpDomain = $subscription_data['domain'];
        $rootPath = "/home/{$ftpUsername}/ftp_rootpath";
    
        $commands = [
            "sudo useradd {$ftpUsername}",
            "echo '{$ftpUsername}:{$ftpDomain}' | sudo tee -a /etc/vsftpd.userlist",
            "echo '{$ftpUsername}:{$ftpPassword}' | sudo chpasswd",
            "sudo mkdir -p {$rootPath}",
            "sudo chown -R {$ftpUsername}: {$rootPath}",
        ];

        foreach ($commands as $command) {
            shell_exec($command);
        }

        HostingSubscriptionFtpAccount::create($subscription_data);

        return [
            'success' => 'Ftp Account created successfully'
        ];


     }

     /**
     * @param string $username
     * @param string|null $domain
     * @return string[]|null
     */
    public static function getFtpAccount(string $username, ?string $domain = null)
    {

        $userListPath = '/etc/vsftpd.userlist';
        
        $command = "cat {$userListPath}";

        exec($command, $userList, $returnCode);

        if ($returnCode === 0) {
            foreach ($userList as $user) {
                $userData = explode(':', $user);

                if ($userData[0] === $username && ($domain === null) || (isset($userData[2]) && $userData[2] === $domain)) {
                    return $userData;
                }
            }
        }
                
        return null;
    }

    /**
     * @param string $username
     * @return array
     */
    public static function deleteFtpAccount(string $username)
    {
        $userListPath = '/etc/vsftpd.userlist';
    
        $command = "cat {$userListPath}";
        exec($command, $userList, $returnCode);

        if ($returnCode === 0) {
            $newUserList = array_filter($userList, function($user) use ($username) {
                return !str_starts_with($user, $username . ':');
            });

            file_put_contents($userListPath, implode(PHP_EOL, $newUserList) . PHP_EOL);
        }

        shell_exec('sudo userdel ' . escapeshellarg($username));

        HostingSubscriptionFtpAccount::where('ftp_username', $username)->delete();
        
        $check_deleted = shell_exec('id ' . escapeshellarg($username));

        if ($check_deleted !== null) {
            return [
                'error' => true,
                'message' => 'Failed to delete user from the system.',
            ];
        }

        return [
            'success' => 'User deleted successfully',
        ];
    }

}