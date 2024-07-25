<?php

namespace App\Models;

use App\Services\Fail2Ban\Fail2BanWhitelistIp\Fail2BanWhitelistIpService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fail2BanWhitelistedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'comment'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            Fail2BanWhitelistIpService::addWhitelistedIpToJailConf($model->ip);
        });

        static::deleting(function ($model) {
            Fail2BanWhitelistIpService::removeWhiteListedIpFromJailConf($model->ip);
        });
    }
}
