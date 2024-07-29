<?php

namespace App\Models;

use App\Services\Fail2Ban\Fail2BanBannedIp\Fail2BanBannedIpService;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Fail2BanBannedIp extends Model
{

    use Sushi;

    protected $fillable = [
        'ip',
        'status',
        'banned_date'
    ];

    protected $schema = [
        'id' => 'integer',
        'ip' => 'string',
        'status' => 'string',
        'service' => 'string',
        'ban_date' => 'string'
    ];

    public function getRows()
    {
        $bannedIps = Fail2BanBannedIpService::getIps();

        return array_map(function ($bannedIps, $index) {
            return [
                'id' => $index + 1,
                'ip' => $bannedIps['ip'],
                'status' => $bannedIps['status'],
                'service' => $bannedIps['service'],
                'ban_date' => $bannedIps['ban_date'],
            ];
        }, $bannedIps, array_keys($bannedIps));


    }
}
