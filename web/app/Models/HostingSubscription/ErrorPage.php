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

    protected $table = 'error_pages';

    public static function boot()
    {
        parent::boot();
        static::hostingSubscriptionErrorPage();
    }

    public static function hostingSubscriptionErrorPage()
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
