<?php

namespace App\Models;

use App\DirectoryTreeBuildTrate;
use App\Jobs\HtaccessBuildIndexes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    use HasFactory, DirectoryTreeBuildTrate;

    protected $fillable = [
        'hosting_subscription_id',
        'directory',
        'index_type'
    ];

    protected $table = 'indices';

    public static function boot() {
        parent::boot();
        static::indexesBoot();
    }

    public static function indexesBoot() {

        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::creating(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
        });

        $callback = function ($model) use ($hostingSubscription) {
            $htaccessBuild = new HtaccessBuildIndexes(false, $model, $hostingSubscription);
            $htaccessBuild->handle();
        };

        static::created($callback);
        static::updated($callback);

        static::deleted(function ($model) use ($hostingSubscription) {
            $htaccessBuild = new HtaccessBuildIndexes(false, $model, $hostingSubscription);
            $htaccessBuild->isDeleted(true);
            $htaccessBuild->handle();
        });
    }
}
