<?php

namespace App\Server\Helpers;

use App\Server\Helpers\CloudLinux\CloudLinuxPHPHelper;
use App\Server\Installers\CloudLinux\CloudLinuxInstaller;

class PHP
{
    public static function getPHPVersion($phpVersionFull)
    {
        $phpVersions = self::getInstalledPHPVersions();
        foreach ($phpVersions as $version) {
            if ($version['full'] == $phpVersionFull) {
                return $version;
            }
        }
        return null;
    }

    public static function getInstalledPHPVersions()
    {
        $phpVersions = [];

        $isCloudLinuxInstalled = CloudLinuxInstaller::isCloudLinuxInstalled();
        if ($isCloudLinuxInstalled) {
            $getCloudLinuxPHPVersions = CloudLinuxPHPHelper::getSupportedPHPVersions();
            if (isset($getCloudLinuxPHPVersions['data'])) {
                foreach ($getCloudLinuxPHPVersions['data'] as $version) {

                    $binPath = $version['path'];
                    $binPath = str_replace('/usr/bin/php-cgi', '/usr/bin/', $binPath);
                    $shortWithoutDot = str_replace('.', '', $version['short']);
                    $fileType = 'application/x-httpd-php' . $shortWithoutDot;
                    $fileExtensions = '.php .php' . substr($shortWithoutDot,0,1) . ' .phtml';
                    $phpVersions[] = [
                        'short' => $version['short'],
                        'shortWithoutDot' => $shortWithoutDot,
                        'full' => $version['full'],
                        'path' => $version['path'],
                        'friendlyName' => 'PHP ' . $version['short'],
                        'vendor' => 'CloudLinux',
                        'binPath'=>$binPath,
                        'scriptAlias' => '/cgi-php-' . $shortWithoutDot . ' ' . $binPath,
                        'action' => $fileType . ' /cgi-php-' . $shortWithoutDot . '/php-cgi',
                        'fileType' => $fileType,
                        'fileExtensions' => $fileExtensions,
                    ];

                }
            }
        }

        return $phpVersions;
    }
}
