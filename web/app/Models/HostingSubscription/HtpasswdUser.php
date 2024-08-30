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

    public static function boot()
    {
        parent::boot();
        static::htpasswdUserBoot();
    }

    public static function htpasswdUserBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $directoryRealPath = "/home/{$hostingSubscription->system_username}/.htpasswd";
        static::creating(function ($model) use ($hostingSubscription, $directoryRealPath) {
            $command = "htpasswd -nb $model->username $model->password";
            $result = shell_exec($command);
            if ($result) {
                list($user, $hashedPasswd) = explode(':', trim($result), 2);
                $model->password = $hashedPasswd;
            }
        });

        $startComment = '# Section managed by Panel Omega: Directory Privacy, do not edit';
        $endComment = '# End section managed by Panel Omega: Directory Privacy';

        $callback = function ($model = null) use ($hostingSubscription, $directoryRealPath, $startComment, $endComment) {
            $directoryPrivacy = new HtpasswdBuild(false, $directoryRealPath, $hostingSubscription->id, $startComment, $endComment);
            $directoryPrivacy->handle($model);
        };

        static::created(function ($model) use ($callback) {
            $callback($model);
        });

        static::deleting(function ($model) use ($hostingSubscription) {
            $command = "htpasswd -D /home/{$hostingSubscription->system_username}/.htpasswd {$model->username}";
            shell_exec($command);
        });

        static::deleted(function ($model) use ($callback) {
            $callback();
        });
    }

    public function directoryPrivacy()
    {
        return $this->belongsTo(DirectoryPrivacy::class, 'directory', 'directory');
    }
}
