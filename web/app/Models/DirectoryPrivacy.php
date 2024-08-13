<?php

namespace App\Models;

use App\Jobs\DirectoryPrivacyHtConfigBuild;
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
        static::DirectoryPrivacyBoot();
    }

    public static function DirectoryPrivacyBoot()
    {
        $hostingSubscriptionId = Session::get('hosting_subscription_id');

        static::saving(function ($model) use ($hostingSubscriptionId) {
            $model->hosting_subscription_id = $hostingSubscriptionId;
            $model->password = Crypt::encrypt($model->password);
        });

        static::created(function ($model) use ($hostingSubscriptionId) {
            $directoryPrivacy = new DirectoryPrivacyHtConfigBuild(false, $hostingSubscriptionId);
            $directoryPrivacy->handle();
        });

        $callback = function ($model) use ($hostingSubscriptionId) {
            $directoryPrivacy = new DirectoryPrivacyHtConfigBuild(false, $hostingSubscriptionId);
            $directoryPrivacy->handle($model);
        };

        static::updated($callback);
        static::deleted($callback);
    }

    public static function decryptPassword($password)
    {
        return $password ? Crypt::decrypt($password) : null;
    }

    public static function scanUserDirectories()
    {
        $customer = Customer::getHostingSubscriptionSession();
        $username = $customer['system_username'];
        $baseDir = '/home/' . $username;
        $command = "find $baseDir -type d";
        $userDirs = shell_exec($command);
        $userDirsArray = array_filter(explode(PHP_EOL, trim($userDirs)));

        $filteredDirs = array_map(function ($dir) use ($baseDir) {
            $relativeDir = str_replace($baseDir, '', $dir);

            return $relativeDir === '' ? '/' : $relativeDir;
        }, $userDirsArray);

        if (count($filteredDirs) === 1 && $filteredDirs[0] === '/') {
            $filteredDirs = ['/'];
        }
        return $filteredDirs;
    }

    public function indexes()
    {
        return $this->hasMany(Index::class, 'directory', 'directory')
            ->where('hosting_subscription_id', $this->hosting_subscription_id);
    }
}
