<?php

namespace App\Services\FileManager\PermissionsService;

use App\Models\Customer;
use Illuminate\Support\Facades\Storage;

class PermissionsManager
{
    public const USER_FILE_PERMISSION = 644;
    public const USER_DIRECTORY_PERMISSION = 711;

    public static function setFilePermissions(string $path)
    {
        $permissions = self::USER_FILE_PERMISSION;

        $hasDirectories = self::checkDirectories(dirname($path));

        if ($hasDirectories && !self::setDirectoryPermissions($path)) {

            return false;
        }

        return self::setPermissions($path, $permissions);

    }

    public static function setDirectoryPermissions(string $path)
    {
        $permissions = self::USER_DIRECTORY_PERMISSION;

        $dirPaths = preg_split('/\//', $path, -1, PREG_SPLIT_NO_EMPTY);
        $currentPath = '';

        foreach ($dirPaths as $key => $dirPath) {

            if (!str_contains($dirPath, '.')) {
                if ($dirPath !== '.') {
                    $currentPath .= $dirPath . '/';
                    $setPermissions = self::setPermissions($currentPath, $permissions);
                    if (!$setPermissions) {
                        return false;
                    }
                }
            }

        }
        return true;
    }

    public static function checkDirectories(string $path)
    {

        if ($path !== '.') {
            return true;
        }

        return false;
    }

    public static function setPermissions(string $path, int $permissions)
    {

        $hostingSubscription = self::getHostingSubscription();
        if (isset($hostingSubscription['error'])) {
            return false;
        }

        $username = $hostingSubscription->system_username;
        $groupname = $hostingSubscription->system_username;

        if ($path !== 'public_html/') {

            $currentPermissions = substr(sprintf("%o", fileperms('/home/' . $username . '/' . $path)), -4);

            if (decoct($currentPermissions) !== $permissions) {

                $commands = [
                    "sudo chmod {$permissions} /home/{$username}/{$path}",
                    "sudo chown {$username}:{$groupname} /home/{$username}/{$path}"
                ];

                foreach ($commands as $command) {
                    $output = shell_exec($command);
                    if ($output !== null) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public static function getHostingSubscription(): ?object
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        if (empty($hostingSubscription)) {
            return [
                'error' => true,
                'message' => 'Hosting subscription not found.'
            ];
        }

        return $hostingSubscription;
    }
}
