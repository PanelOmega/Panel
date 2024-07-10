<?php

namespace App\Server\Helpers;

use App\Models\HostingSubscriptionFtpAccount;

class FtpAccount {

    /**
     * @param HostingSubscriptionFtpAccount $model
     * @return array
     */
     public static function createFtpAccount(HostingSubscriptionFtpAccount $model): array {

         if (!self::checkFtpConnection()) {
             return [
                 'error' => true,
                 'message' => 'Failed to start FTP server.'
             ];
         }

        $checkFtpUser = self::getFtpAccount($model->ftp_username);

        if (!empty($checkFtpUser)) {
            return [
                'error' => true,
                'message' => 'Ftp account already exists.'
            ];
        }

        $ftpUsername = $model->ftp_username;
        $ftpPassword = $model->ftp_password;
        $ftpPath = $model->ftp_path;
        $rootPath = "/home/{$ftpUsername}/{$ftpPath}";

        $commands = [
            "sudo useradd {$ftpUsername}",
            "echo '{$ftpUsername}' | sudo tee -a /etc/vsftpd/user_list",
            "echo '{$ftpUsername}:{$ftpPassword}' | sudo chpasswd",
            "sudo mkdir -p {$rootPath}",
            "sudo chown -R {$ftpUsername}: {$rootPath}",
        ];

        foreach ($commands as $command) {
            shell_exec($command);
        }

        return [
            'success' => 'Ftp Account created successfully'
        ];

     }

     /**
     * @param string $username
     * @return string[]|null
     */
    public static function getFtpAccount(string $username)
    {

        $userListPath = '/etc/vsftpd/user_list';

        $command = "cat {$userListPath}";

        exec($command, $userList, $returnCode);

        if ($returnCode === 0) {
            foreach ($userList as $user) {
                $userData = explode(':', $user);

                if ($userData[0] === $username || (isset($userData[2]))) {
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
    public static function deleteFtpAccount(string $username): array
    {
        $userListPath = '/etc/vsftpd/user_list';

        $commands = [
            "grep -v '^{$username}:' {$userListPath} > {$userListPath}.tmp && mv {$userListPath}.tmp {$userListPath}",
            "sudo userdel " . escapeshellarg($username)
        ];

        foreach($commands as $command) {
            shell_exec($command);
        }

        $checkDeleted = shell_exec('id ' . escapeshellarg($username));

        if ($checkDeleted !== null) {
            return [
                'error' => true,
                'message' => 'Failed to delete user from the system.',
            ];
        }

        return [
            'success' => 'User deleted successfully',
        ];

    }

    /**
     * @return bool
     */
    public static function checkFtpConnection(): bool {

        $isFtpServerActive = function () {
            return trim(shell_exec('sudo systemctl is-active vsftpd')) === 'active';
        };

        if (!$isFtpServerActive()) {
            shell_exec('sudo systemctl start vsftpd');
        }

        return $isFtpServerActive();
    }

}
