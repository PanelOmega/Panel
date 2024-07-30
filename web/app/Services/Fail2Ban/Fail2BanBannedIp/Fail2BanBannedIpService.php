<?php

namespace App\Services\Fail2Ban\Fail2BanBannedIp;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Fail2BanBannedIpService
{
    public static function getIps()
    {
        $connection = DB::connectUsing('fail2ban', [
            'driver' => 'sqlite',
            'database' => '/var/lib/fail2ban/fail2ban.sqlite3',
        ]);

        $getJails = $connection->table('bips')->get();
        $getJails = json_decode(json_encode($getJails), true);

        $bannedIps = [];
        foreach ($getJails as $jail) {
            $bannedIps[] = [
                'ip' => $jail['ip'],
                'status' => 'BANNED',
                'service' => $jail['jail'],
                'ban_date' => Carbon::createFromTimestamp($jail['timeofban']),
                'ban_time' => $jail['bantime'],
                'ban_count' => $jail['bancount'],
            ];
        }
        return $bannedIps;
    }

    public static function getJailStatus(string $jail): string
    {
        $command = 'fail2ban-client status ' . $jail;
        return shell_exec($command);
    }

    public static function unBanIP(string $ip, string $service): bool
    {
        $command = 'fail2ban-client set ' . $service . ' unbanip ' . $ip;
        $result = shell_exec($command);

        return trim($result) === '1';
    }

    public static function banIP(string $ip, string $service): bool
    {
        $command = 'fail2ban-client set ' . $service . ' banip ' . $ip;
        $result = shell_exec($command);

        return trim($result) === '1';
    }

}
