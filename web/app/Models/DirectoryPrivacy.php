<?php

namespace App\Models;

use App\Jobs\HtaccessBuildDirectoryPrivacy;
use Illuminate\Database\Eloquent\Model;

class DirectoryPrivacy extends Model
{

    protected $fillable = [
        'hosting_subscription_id',
        'directory',
        'username',
        'password',
        'protected',
        'label',
        'path'
    ];

    protected static function boot()
    {
        parent::boot();
        static::DirectoryPrivacyBoot();
    }

    public static function DirectoryPrivacyBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::creating(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
        });
        $callback = function ($model = null) use ($hostingSubscription) {
            $directoryRealPath = null;
            if ($model) {
                $storage = $model->storageInstance();
                $directoryRealPath = $storage->path($model->path);
            }
            dd($model);
            $directoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $directoryRealPath, $hostingSubscription->id);
            $directoryPrivacy->handle($model);
        };

        static::created(function () use ($callback) {
            $callback();
        });
        static::updated(function ($model) use ($callback) {
            $callback($model);
        });
        static::deleted(function ($model) use ($callback) {
            $callback($model);
        });
    }

    public function htpasswdUser()
    {
        return $this->hasOne(HtpasswdUser::class);
    }
}
