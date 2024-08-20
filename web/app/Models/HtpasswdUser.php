<?php

namespace App\Models;

use App\Jobs\HtpasswdBuild;
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
        static::HtpasswdUserBoot();
    }

    public static function HtpasswdUserBoot()
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

//    public function getUsers($directoryPath)
//    {
//        $userRecords = [];
//        if (file_exists($directoryPath)) {
//            $pattern = '/^(?!\s*#).+$/';
//            $lines = file($directoryPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//
//            $index = 1;
//
//            foreach ($lines as $line) {
//                if (preg_match($pattern, $line)) {
//                    $user = explode(':', $line);
//                    $userRecords[] = [
//                        'id' => $index,
//                        'directory' => null,
//                        'username' => $user[0],
//                        'password' => null,
//                    ];
//                    $index++;
//                }
//
//            }
//        }
//        return $userRecords;
//    }

    public function directoryPrivacy()
    {
        return $this->belongsTo(DirectoryPrivacy::class, 'directory', 'directory');
    }
}
