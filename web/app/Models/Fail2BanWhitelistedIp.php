<?php

namespace App\Models;

use App\Jobs\Fail2BanConfigBuild;
use App\Services\Fail2Ban\Fail2BanResetTable\Fail2BanResetTableService;
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
        static::fail2BanCallbacks();
    }

    protected static function fail2BanCallbacks() {

        $callback = function($model) {
            $fail2banConfig = new Fail2BanConfigBuild();
            $fail2banConfig->handle();
        };

        static::created($callback);
        static::updated($callback);
        static::deleted($callback);
    }
}
