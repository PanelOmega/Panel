<?php

namespace App\Models;

use App\Services\Fail2Ban\Fail2BanBannedIp\Fail2BanBannedIpService;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Fail2BanBannedIp extends Model
{
    use Sushi;

    protected $fillable = [
        'ip',
        'status',
        'service',
        'banned_date',
        'banned_time',
    ];

    protected $schema = [
        'id' => 'integer',
        'ip' => 'string',
        'status' => 'string',
        'service' => 'string',
        'ban_date' => 'string',
        'ban_time' => 'string',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $result = Fail2BanBannedIpService::banIP($model->ip, $model->service);
//            if (!$result) {
//                throw new \Exception('Failed to ban IP');
//            }
        });

        static::deleting(function ($model) {
            $result = Fail2BanBannedIpService::unBanIP($model->ip, $model->service);
//            if (!$result) {
//                throw new \Exception('Failed to unban IP');
//            }
        });
    }

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
                'ban_time' => $this->secondsToHumanReadable($bannedIps['ban_time'])
            ];
        }, $bannedIps, array_keys($bannedIps));

    }

    public function secondsToHumanReadable($seconds)
    {
        return CarbonInterval::seconds($seconds)->cascade()->forHumans();
    }
}
