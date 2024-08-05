<?php

namespace App\Models;

use App\Jobs\ApacheHtFilesBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotlinkProtection extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'url_allow_access',
        'block_extensions',
        'allow_direct_requests',
        'redirect_to',
        'enabled'
    ];

    public static function boot()
    {
        parent::boot();
        static::hotlinkProtectionBoot();
    }

    public static function hotlinkProtectionBoot()
    {
        $callback = function ($model) {
            $hotlinkProtection = new ApacheHtFilesBuild(false, $model->hosting_subscription_id);
            $hotlinkProtection->handle($model);
        };
        static::saving($callback);
    }
}
