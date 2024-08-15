<?php

namespace App\Models;

use App\DirectoryTreeBuildTrate;
use App\Jobs\HtaccessBuildDirectoryPrivacy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectoryPrivacy extends Model
{
    use HasFactory, DirectoryTreeBuildTrate;

    protected $fillable = [
        'hosting_subscription_id',
        'directory',
        'username',
        'password',
        'protected',
        'label'
    ];

    public static function boot()
    {
        parent::boot();
        static::DirectoryPrivacyBoot();
    }

    public static function DirectoryPrivacyBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::saving(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
            $command = "htpasswd -nb $model->username $model->password";
            $result = shell_exec($command);
            if($result) {
                list($user, $hashedPasswd) = explode(':', trim($result), 2);
                $model->password = $hashedPasswd;
            }
        });

        $callback = function ($model = null) use ($hostingSubscription) {
            $directoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $hostingSubscription->id);
            $directoryPrivacy->handle($model);
        };

        static::created(function() use ($callback) {
            $callback();
        });
        static::updated(function($model) use ($callback){
            $callback($model);
        });
        static::deleted(function($model) use ($callback) {
            $callback($model);
        });
    }
}
