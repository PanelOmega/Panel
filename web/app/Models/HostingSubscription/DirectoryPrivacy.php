<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildDirectoryPrivacy;
use App\Models\Customer;
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
        static::directoryPrivacyBoot();
    }

    public static function directoryPrivacyBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        static::creating(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
            HtpasswdUser::create([
                'directory' => $model->directory,
                'username' => $model->username,
                'password' => $model->password,
            ]);
        });
        $callback = function ($model) use ($hostingSubscription) {
            $directoryRealPath = "/home/{$hostingSubscription->system_username}/public_html/{$model->path}";
            $directoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $directoryRealPath, $hostingSubscription->id);
            $directoryPrivacy->handle($model);
        };

        static::created(function ($model) use ($callback) {
            $callback($model);
        });

        static::updated(function ($model) use ($callback) {
            $callback($model);
        });

        static::deleting(function ($model) {
            $htpasswdUser = HtpasswdUser::where('username', $model->username)->first();
            $htpasswdUser->delete();
        });

        static::deleted(function ($model) use ($callback) {
            $callback($model);
        });
    }

    public function htpasswdUser()
    {
        return $this->hasOne(HtpasswdUser::class, 'directory', ' directory');
    }
}
