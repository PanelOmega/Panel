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

    protected $table = 'hosting_subscription_directory_privacies';

    protected static function boot()
    {
        parent::boot();
        static::directoryPrivacyBoot();
    }

    public static function directoryPrivacyBoot()
    {

        static::creating(function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $model->hosting_subscription_id = $hostingSubscription->id;
            HtpasswdUser::create([
                'directory' => $model->directory,
                'username' => $model->username,
                'password' => $model->password,
            ]);
        });
        $callback = function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $directoryRealPath = "/home/{$hostingSubscription->system_username}/public_html/{$model->path}";

            $directoryPrivacyModelData = [
                'protected' => $model->protected,
                'label' => $model->label
            ];
            $directoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $directoryRealPath, $hostingSubscription->id, $directoryPrivacyModelData);
            $directoryPrivacy->handle();
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

        static::deleted(function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $directoryRealPath = "/home/{$hostingSubscription->system_username}/public_html/{$model->path}";
            $directoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $directoryRealPath, $hostingSubscription->id, []);
            $directoryPrivacy->handle();
        });
    }

    public function htpasswdUser()
    {
        return $this->hasOne(HtpasswdUser::class, 'directory', ' directory');
    }
}
