<?php

namespace App\Server\Helpers;

use App\Server\Helpers\CloudLinux\CloudLinuxPHPHelper;
use App\Server\Installers\CloudLinux\CloudLinuxInstaller;

class PHP
{
    public static function getInstalledPHPVersions()
    {
        $phpVersions = [];

        $isCloudLinuxInstalled = CloudLinuxInstaller::isCloudLinuxInstalled();
        if ($isCloudLinuxInstalled) {
            $getCloudLinuxPHPVersions = CloudLinuxPHPHelper::getSupportedPHPVersions();
            if (isset($getCloudLinuxPHPVersions['data'])) {
                foreach ($getCloudLinuxPHPVersions['data'] as $version) {
                    $phpVersions[] = [
                        'short' => $version['short'],
                        'full' => $version['full'],
                        'path' => $version['path'],
                        'friendlyName' => 'PHP ' . $version['short'],
                        'vendor' => 'CloudLinux'
                    ];
                }
            }
        }

        return $phpVersions;
    }
}
