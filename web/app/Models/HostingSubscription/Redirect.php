<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildRedirects;
use App\Models\Customer;
use App\Models\Traits\RedirectTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory, RedirectTrait;

    protected $fillable = [
        'hosting_subscription_id',
        'status_code',
        'type',
        'domain',
        'directory',
        'regular_expression',
        'redirect_url',
        'match_www',
        'wildcard'
    ];

    protected static function boot()
    {
        parent::boot();
        static::redirectsBoot();
    }

    public static function redirectsBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::creating(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
            $pattern = '/^(\w+)_(\d+)$/';
            if (preg_match($pattern, $model->type, $matches)) {
                $model->type = $matches[1];
                $model->status_code = $matches[2];
            } else {
                $model->type = null;
                $model->status_code = null;
            }

            $model->directory === null ? $model->directory = '/' : '';
        });

        $callback = function () use ($hostingSubscription) {
            $redirectionsPath = "/home/{$hostingSubscription->system_username}/public_html/.htaccess";
            $redirectionsBuild = new HtaccessBuildRedirects(false, $redirectionsPath, $hostingSubscription->id);
            $redirectionsBuild->handle();
        };

        static::created($callback);
        static::deleted($callback);

    }
}
