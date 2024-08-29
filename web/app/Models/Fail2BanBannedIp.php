<?php

namespace App\Models;

use App\Services\Fail2Ban\Fail2BanBannedIp\Fail2BanBannedIpService;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;

class Fail2BanBannedIp extends Model
{
//    use Sushi;

    protected $fillable = [
        'hosting_subscription_id',
        'ip',
        'status',
        'service',
        'ban_count',
        'banned_date',
        'banned_time',
    ];

    public static function boot()
    {
        parent::boot();
        static::Fail2BanBannedIpBoot();
    }

    public static function Fail2BanBannedIpBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::creating(function ($model) use ($hostingSubscription) {
            $result = Fail2BanBannedIpService::banIP($model->ip, $model->service);
            if (!$result) {
                throw new \Exception('Failed to ban IP');
            }

            $bannedIp = Fail2BanBannedIpService::getBannedIp();

            $model->hosting_subscription_id = $hostingSubscription->id;
            $model->status = $bannedIp['status'];
            $model->ban_count = $bannedIp['ban_count'];
            $model->ban_date = $bannedIp['ban_date'];
            $model->ban_time = self::secondsToHumanReadable($bannedIp['ban_time']);

        });

        static::deleting(function ($model) {
            $result = Fail2BanBannedIpService::unBanIP($model->ip, $model->service);
            if (!$result) {
                throw new \Exception('Failed to unban IP');
            }
        });
    }

    public static function secondsToHumanReadable($seconds)
    {
        return CarbonInterval::seconds($seconds)->cascade()->forHumans();
    }
}
