<?php

namespace App\Server\Helpers;

class OS
{
    public const ALMA_LINUX = 'AlmaLinux';
    public const CENTOS = 'CentOS';
    public const DEBIAN = 'Debian';

    public const UBUNTU = 'Ubuntu';

    public const UNKNOWN = 'Unknown';

    public static function getDistro()
    {
        $os = shell_exec('lsb_release -a');
        if (strpos($os, 'AlmaLinux') !== false) {
            return self::ALMA_LINUX;
        } elseif (strpos($os, 'CentOS') !== false) {
            return self::CENTOS;
        } elseif (strpos($os, 'Debian') !== false) {
            return self::DEBIAN;
        } elseif (strpos($os, 'Ubuntu') !== false) {
            return self::UBUNTU;
        }

    }
}
