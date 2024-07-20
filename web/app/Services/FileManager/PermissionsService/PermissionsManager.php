<?php

namespace App\Services\FileManager\PermissionsService;

use App\Models\Customer;

class PermissionsManager
{
    public const USER_FILE_PERMISSION = 644;
    public const USER_DIRECTORY_PERMISSION = 711;
    public const DEFAULT_PERMISSIONS = 700;

    public static function setPermissions(string $path): bool
    {

        $dirPaths = preg_split('/[\/\\\\]/', $path, -1, PREG_SPLIT_NO_EMPTY);
        $currentPath = '';

        foreach ($dirPaths as $key => $dirPath) {
            if (!str_contains($dirPath, '.')) {
                $currentPath .= $dirPath . '/';

            } else {
                $currentPath .= $dirPath;
            }

            if (!self::set($currentPath)) {
                return false;
            }
        }

        return true;
    }

    public static function setUnzipPermissions($path)
    {
        $dirPaths = preg_split('/[\/\\\\]/', $path, -1, PREG_SPLIT_NO_EMPTY);

        $currentPath = '';

        foreach ($dirPaths as $key => $dirPath) {
            if (!str_contains($dirPath, '.')) {
                $currentPath .= $dirPath . '/';

                if (!self::setUnzip($currentPath)) {
                    return false;
                }
            }
        }

        return true;
    }


    public static function set(string $path): bool
    {
        $hostingSubscription = self::getHostingSubscription();

        if (isset($hostingSubscription['error'])) {
            return false;
        }

        $permissions = [
            'file' => self::USER_FILE_PERMISSION,
            'dir' => self::USER_DIRECTORY_PERMISSION,
            'default' => self::DEFAULT_PERMISSIONS,
        ];

        $fullPath = '/home/' . $hostingSubscription['user_name'] . '/' . $path;
        $currentPermissions = substr(sprintf("%o", fileperms($fullPath)), -4);

        $isFile = str_contains(basename($path), '.');
        $isDefaultPermission = $currentPermissions == $permissions['default'];

        $commands = [];

        if ($isDefaultPermission && !$isFile) {
            $commands[] = "sudo chmod {$permissions['dir']} $fullPath";
        }

        if ($isFile) {
            $commands[] = "sudo chmod {$permissions['file']} $fullPath";
        }

        $commands[] = "sudo chown -R {$hostingSubscription['user_name']}:{$hostingSubscription['group_name']} $fullPath";

        foreach ($commands as $command) {
            $output = shell_exec($command);
            if ($output !== null && $output !== '') {
                return false;
            }
        }

        return true;
    }

    public static function setUnzip(string $path): bool
    {
        $hostingSubscription = self::getHostingSubscription();

        if (isset($hostingSubscription['error'])) {
            return false;
        }

        $permissions = [
            'dir' => self::USER_DIRECTORY_PERMISSION
        ];

        $commands = [
            "sudo chmod {$permissions['dir']} /home/{$hostingSubscription['user_name']}/{$path}",
            "sudo chown -R {$hostingSubscription['user_name']}:{$hostingSubscription['group_name']} /home/{$hostingSubscription['user_name']}/{$path}"
        ];

        foreach ($commands as $command) {
            $output = shell_exec($command);
            if ($output !== null) {
                return false;
            }
        }

        return true;
    }

    public static function getHostingSubscription(): array
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        if (empty($hostingSubscription)) {
            return [
                'error' => true,
                'message' => 'Hosting subscription not found.'
            ];
        }

        return [
            'user_name' => $hostingSubscription->system_username,
            'group_name' => $hostingSubscription->system_username,
        ];
    }
}
