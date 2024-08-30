<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildErrorPage;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'name',
        'error_code',
        'content',
        'path'
    ];

    protected $table = 'hosting_subscription_error_pages';

    public static function boot()
    {
        parent::boot();
        static::errorPageBoot();
    }

    public static function errorPageBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        $callback = function ($model) use ($hostingSubscription) {
            $errorPageBuild = new HtaccessBuildErrorPage(false, $hostingSubscription);
            $errorPageBuild->handle($model);
        };

        static::created($callback);
        static::updated($callback);
    }

}
