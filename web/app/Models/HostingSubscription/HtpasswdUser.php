<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtpasswdBuild;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HtpasswdUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'directory',
        'username',
        'password'
    ];

    protected $table = 'hosting_subscription_htpasswd_users';

    public static function boot()
    {
        parent::boot();
        static::htpasswdUserBoot();
    }

    public static function htpasswdUserBoot()
    {
        static::creating(function ($model) {
            $command = "htpasswd -nb $model->username $model->password";
            $result = shell_exec($command);
            if ($result) {
                list($user, $hashedPasswd) = explode(':', trim($result), 2);
                $model->password = $hashedPasswd;
            }
        });

        $callback = function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $directoryRealPath = "/home/{$hostingSubscription->system_username}/.htpasswd";
            $htPasswdData = [
                'username' => $model->username,
                'password' => $model->password
            ];
            $directoryPrivacy = new HtpasswdBuild(false, $directoryRealPath, $htPasswdData);
            $directoryPrivacy->handle();
        };

        static::created(function ($model) use ($callback) {
            $callback($model);
        });

        static::deleting(function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $command = "htpasswd -D /home/{$hostingSubscription->system_username}/.htpasswd {$model->username}";
            shell_exec($command);
        });

        static::deleted(function ($model) use ($callback) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $directoryRealPath = "/home/{$hostingSubscription->system_username}/.htpasswd";
            $directoryPrivacy = new HtpasswdBuild(false, $directoryRealPath);
            $directoryPrivacy->handle();
        });
    }

    public function directoryPrivacy()
    {
        return $this->belongsTo(DirectoryPrivacy::class, 'directory', 'directory');
    }
}
