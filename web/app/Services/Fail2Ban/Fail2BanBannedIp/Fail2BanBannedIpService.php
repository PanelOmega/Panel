<?php

namespace App\Services\Fail2Ban\Fail2BanBannedIp;

class Fail2BanBannedIpService
{
    public static function getIps()
    {
        $jails = self::getJails();

        $bannedIps = [];

        foreach ($jails as $jail) {
            $jailStatus = self::getJailStatus($jail);
            $ips = self::getBannedIps($jailStatus);
            $banDates = self::getBanDates($jail, $ips);

            foreach ($ips as $ip) {
                $bannedIps[] = [
                    'ip' => $ip,
                    'status' => 'BANNED',
                    'service' => $jail,
                    'ban_date' => $banDates[$ip] ?? '',
                ];
            }
        }
        return $bannedIps;
    }

    public static function getJails(): array
    {
        $command = 'fail2ban-client status';
        $status = shell_exec($command);

        if (preg_match('/Jail list:\s*(.*)/', $status, $matches)) {
            $jails = $matches[1];
            return array_map('trim', explode(',', $jails));
        }

        return [];
    }

    public static function getJailStatus(string $jail): string
    {
        $command = 'fail2ban-client status ' . $jail;
//        dd(shell_exec($command));
        return shell_exec($command);
    }

    public static function getBannedIps(string $jailStatus): array
    {
        if (preg_match('/Banned IP list:\s*(.*)/', $jailStatus, $matches)) {
            if (!empty($matches[1])) {
                return array_map('trim', explode(',', $matches[1]));
            }
        }

        return [];
    }

    public static function getBanDates(string $jail, array $ips): array
    {
        $logFile = '/var/log/fail2ban.log';
        $banDates = [];

        foreach ($ips as $ip) {
            $command = "grep 'Ban' {$logFile} | grep '{$ip}' | grep '{$jail}' | tail -n 1";
            $output = shell_exec($command);

            if ($output) {
                $pattern = '/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/';

                if (preg_match($pattern, trim($output), $matches)) {
                    $banDates[$ip] = $matches[1];
                }
            }
        }
        return $banDates;
    }

    public static function unBanIP(string $ip, string $service): bool
    {
        $command = 'fail2ban-client set ' . $service . ' unbanip ' . $ip;
        $result = shell_exec($command);

        return trim($result) === '1';
    }
}
