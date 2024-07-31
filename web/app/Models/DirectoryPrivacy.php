<?php

namespace App\Models;

use App\Jobs\DirectoryPrivacyHtFilesBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class DirectoryPrivacy extends Model
{
    use HasFactory;

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

        $hostingSubscriptionId = Session::get('hosting_subscription_id');

        static::saving(function ($model) use ($hostingSubscriptionId) {
            $model->hosting_subscription_id = $hostingSubscriptionId;
            $model->password = Crypt::encrypt($model->password);
        });

        static::deleting(function ($model) use ($hostingSubscriptionId) {
            $directoryPrivacy = new DirectoryPrivacyHtFilesBuild();
            $directoryPrivacy->handle($hostingSubscriptionId, $model);
        });

        static::DirectoryPrivacyBoot($hostingSubscriptionId);
    }

    public static function DirectoryPrivacyBoot($hostingSubscriptionId)
    {
        $callback = function ($model) use ($hostingSubscriptionId) {
            $directoryPrivacy = new DirectoryPrivacyHtFilesBuild();
            $directoryPrivacy->handle($hostingSubscriptionId);
        };

        static::created($callback);
        static::updated($callback);
    }

    public static function decryptPassword($password) {
        return $password ? Crypt::decrypt($password) : null;
    }

    public static function scanUserDirectories()
    {
        $customer = Customer::getHostingSubscriptionSession();
        $username = $customer['system_username'];
        $baseDir = '/home/' . $username;
        $command = "find $baseDir -type d";
        $userDirs = shell_exec($command);

        return array_filter(explode(PHP_EOL, trim($userDirs)));
    }
}
