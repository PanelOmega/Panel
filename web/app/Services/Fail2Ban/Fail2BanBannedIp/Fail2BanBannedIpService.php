<?php

namespace App\Services\Fail2Ban\Fail2BanBannedIp;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Fail2BanBannedIpService
{
    public static function getBannedIp()
    {
        $connection = DB::connectUsing('fail2ban', [
            'driver' => 'sqlite',
            'database' => '/var/lib/fail2ban/fail2ban.sqlite3',
        ]);

        $getJails = $connection->table('bips')->get();
        $latestBan = $connection->table('bips')
            ->orderBy('timeofban', 'desc')
            ->first();

        $latestBan = json_decode(json_encode($latestBan), true);

        $latestBannedIp = [
            'ip' => $latestBan['ip'],
            'status' => 'BANNED',
            'service' => $latestBan['jail'],
            'ban_count' => $latestBan['bancount'],
            'ban_date' => Carbon::createFromTimestamp($latestBan['timeofban']),
            'ban_time' => $latestBan['bantime'],
        ];

        return $latestBannedIp;
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
