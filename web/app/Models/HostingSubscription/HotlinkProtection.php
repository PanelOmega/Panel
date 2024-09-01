<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildHotlinkProtection;
use App\Models\Customer;
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
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        $callback = function ($model) use ($hostingSubscription) {
            $hotlinkProtection = new HtaccessBuildHotlinkProtection(false, $hostingSubscription);
            $hotlinkProtection->handle();
        };
        static::saved($callback);
    }
}
