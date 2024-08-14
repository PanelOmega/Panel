<?php

namespace App\Server\Helpers;

use App\Server\Helpers\CloudLinux\CloudLinuxPHPHelper;
use App\Server\Installers\CloudLinux\CloudLinuxInstaller;
use Illuminate\Support\Facades\Cache;

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

                    $checkCopiedFile = '/usr/local/omega/cgi-sys/cl-php' . $shortWithoutDot;
                    if (!is_file($checkCopiedFile)) {
                        shell_exec('mkdir -p /usr/local/omega/cgi-sys');
                        shell_exec('cp ' . $version['path'] . ' ' . $checkCopiedFile);
                        shell_exec('chmod +x ' . $checkCopiedFile);
                        shell_exec('chown root:wheel ' . $checkCopiedFile);
                    }

                    $phpVersions[] = [
                        'short' => $version['short'],
                        'shortWithoutDot' => $shortWithoutDot,
                        'full' => $version['full'],
                        'path' => $checkCopiedFile,
                        'friendlyName' => 'PHP ' . $version['short'],
                        'vendor' => 'CloudLinux',
                        'action' => $fileType . ' /cgi-sys/cl-php' . $shortWithoutDot,
                        'fileType' => $fileType,
                        'fileExtensions' => $fileExtensions,
                    ];

                }
            }
        }

        $getRemiPHPVersions = static::_getRemiPHP();
        if (!empty($getRemiPHPVersions)) {
            $phpVersions = array_merge($phpVersions, $getRemiPHPVersions);
        }
        return $phpVersions;
    }

    private static function _getRemiPHP()
    {

        return Cache::remember('getRemiPHP', 600, function () {

            $phpVersions = [];

            $shellOutput = shell_exec('find / -name php | grep bin');
            $shellOutput = explode("\n", $shellOutput);
            if (!empty($shellOutput)) {
                foreach ($shellOutput as $phpBinPath) {
                    if (!str_contains($phpBinPath, '/opt/remi/')) {
                        continue;
                    }

                    $execCheckPHPVersion = shell_exec($phpBinPath . ' -v');
                    $checkPHPVersion = explode(' ', $execCheckPHPVersion);
                    $checkPHPVersionFull = $checkPHPVersion[1];
                    $checkPHPVersion = substr($checkPHPVersionFull, 0, 3);
                    $shortWithoutDot = str_replace('.', '', $checkPHPVersion);

                    $fileType = 'application/x-httpd-remi-php' . $shortWithoutDot;
                    $fileExtensions = '.php .php' . substr($shortWithoutDot,0,1) . ' .phtml';

                    $checkCopiedFile = '/usr/local/omega/cgi-sys/remi-php' . $shortWithoutDot;
                    if (!is_file($checkCopiedFile)) {
                        shell_exec('mkdir -p /usr/local/omega/cgi-sys');
                        shell_exec('cp ' . $phpBinPath . ' ' . $checkCopiedFile);
                        shell_exec('chmod +x ' . $checkCopiedFile);
                        shell_exec('chown root:wheel ' . $checkCopiedFile);
                    }
                    $checkCopiedCGIFile = '/usr/local/omega/cgi-sys/remi-php' . $shortWithoutDot . '-cgi';
                    if (!is_file($checkCopiedCGIFile)) {
                        $targetCGIFile = '/opt/remi/php'.$shortWithoutDot.'/root/bin/php-cgi';
                        if (is_file($targetCGIFile)) {
                            shell_exec('cp ' . $targetCGIFile . ' ' . $checkCopiedCGIFile);
                            shell_exec('chmod +x ' . $checkCopiedCGIFile);
                            shell_exec('chown root:wheel ' . $checkCopiedCGIFile);
                        }
                    }

                    $phpVersions[] = [
                        'friendlyName' => 'PHP ' . $checkPHPVersionFull . ' (Remi)',
                        'path' => $checkCopiedFile,
                        'short' => $checkPHPVersion,
                        'shortWithoutDot' => $shortWithoutDot,
                        'details' => $execCheckPHPVersion,
                        'fileType' => $fileType,
                        'fileExtensions' => $fileExtensions,
                        'full' => $checkPHPVersionFull,
                        'action' => $fileType . ' /cgi-sys/remi-php' . $shortWithoutDot . '-cgi',
                        'fpmPoolPath'=>'/etc/opt/remi/php' . $shortWithoutDot . '/php-fpm.d',
                        'fpmServiceName'=>'php' . $shortWithoutDot . '-php-fpm'
                    ];
                }
            }

            return $phpVersions;
        });

    }
}
