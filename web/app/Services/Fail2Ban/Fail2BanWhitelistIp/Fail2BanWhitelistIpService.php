<?php

namespace App\Services\Fail2Ban\Fail2BanWhitelistIp;

class Fail2BanWhitelistIpService
{
    public static function addWhitelistedIpToJailConf(string $addIp)
    {
        $pathToConfFile = resource_path('views/server/samples/fail2ban/fail2ban_jail_conf.blade.php');
        $currentWhitelistedIps = file_get_contents($pathToConfFile);

        $ignoreIpPattern = '/^ignoreip\s*=\s*(.*)$/m';

        if (preg_match($ignoreIpPattern, $currentWhitelistedIps, $matches)) {
            $existingIps = trim($matches[1]) ?? '';
        }

        $normalizedIps = preg_replace('/\s*[,\/]\s*/', ', ', $existingIps);
        $ipArray = array_filter(array_map('trim', explode(', ', $normalizedIps)));

        if (!in_array($addIp, $ipArray)) {
            $ipArray[] = $addIp;
        }

        $newIgnoreIpValue = 'ignoreip = ' . implode(', ', $ipArray);
        $newContent = preg_replace($ignoreIpPattern, $newIgnoreIpValue, $currentWhitelistedIps);

        if (file_put_contents($pathToConfFile, $newContent)) {
            $command = 'omega-shell omega:update-fail-2-ban-config';
            shell_exec($command);
        } else {
            throw new \Exception("Failed to write to file " . $pathToConfFile);
        }
    }

    public static function removeWhiteListedIpFromJailConf(string $removeIp)
    {
        $pathToConfFile = resource_path('views/server/samples/fail2ban/fail2ban_jail_conf.blade.php');
        $currentWhitelistedIps = file_get_contents($pathToConfFile);

        $ignoreIpPattern = '/^ignoreip\s*=\s*(.*)$/m';

        if (preg_match($ignoreIpPattern, $currentWhitelistedIps, $matches)) {
            $existingIps = trim($matches[1]) ?? '';
        }

        $normalizedIps = preg_replace('/\s*[,\/]\s*/', ', ', $existingIps);
        $ipArray = array_filter(array_map('trim', explode(', ', $normalizedIps)));

        if (in_array($removeIp, $ipArray)) {
            $ipArray = array_diff($ipArray, [$removeIp]);
        }

        $newIgnoreIpValue = 'ignoreip = ' . implode(', ', $ipArray);
        $newContent = preg_replace($ignoreIpPattern, $newIgnoreIpValue, $currentWhitelistedIps);

        if (file_put_contents($pathToConfFile, $newContent)) {
            $command = 'omega-shell omega:update-fail-2-ban-config';
            shell_exec($command);
        } else {
            throw new \Exception("Failed to write to file " . $pathToConfFile);
        }
    }

}
