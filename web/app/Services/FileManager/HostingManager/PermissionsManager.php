<?php

namespace App\Services\FileManager\HostingManager;

use App\Models\Customer;

class PermissionsManager
{

    public const USER_FILE_PERMISSION = 0644;
    public const USER_DIRECTORY_PERMISSION = 0711;

    public function __construct()
    {

    }

    public function getHostingSubscription(): ?object
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

    public function setFilePermissions(string $path, object $storage)
    {
        $permissions = self::USER_FILE_PERMISSION;
        $this->setOwnerPermission($path, $permissions, $storage);
    }

    public function setDirectoryPermissions(string $path, object $storage)
    {
        $permissions = self::USER_DIRECTORY_PERMISSION;
        $this->setOnwerPermission($path, $permissions, $storage);
    }

    public function setOwnerPermissions(string $path, int $permissions, object $storage)
    {

        $hostingSubscription = $this->getHostingSubscription();

        if (!$hostingSubscription) {
            return;
        }

        $username = $hostingSubscription->system_username;
        $groupname = $hostingSubscription->system_username;

        $storage->setVisibility($path, $permissions);

        $command = "chown {$username}:{$groupname} {$path}";

        $this->execCommand($command);
    }

    public function execCommand($command)
    {
        shell_exec($command);
    }

}
