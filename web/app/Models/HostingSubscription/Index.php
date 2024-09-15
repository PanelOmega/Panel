<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildIndexes;
use App\Models\Customer;
use App\Models\Traits\IndexTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    use HasFactory, IndexTrait;

    protected $fillable = [
        'hosting_subscription_id',
        'directory',
        'directory_real_path',
        'directory_type',
        'index_type'
    ];

    protected $table = 'hosting_subscription_indices';

    public static function boot()
    {
        parent::boot();
        static::indexBoot();
    }

    public static function indexBoot()
    {

        $callback = function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $htaccessBuild = new HtaccessBuildIndexes(false, $hostingSubscription);
            $htaccessBuild->handle($model);
        };

        static::created($callback);
        static::updated($callback);
    }
}
