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

    protected $table = 'hosting_subscription_hotlink_protections';

    public static function boot()
    {
        parent::boot();
        static::hotlinkProtectionBoot();
    }

    public static function hotlinkProtectionBoot()
    {
        $callback = function () {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $hotlinkProtection = new HtaccessBuildHotlinkProtection(false, $hostingSubscription->id);
            $hotlinkProtection->handle();
        };
        static::created($callback);
        static::updated($callback);
        static::deleted($callback);
    }
}
