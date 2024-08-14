<?php

namespace App\Models;

use App\Jobs\HtaccessBuildIndexes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'directory',
        'index_type'
    ];

    protected $table = 'indices';

    public static function boot() {
        parent::boot();

        static::loadDirectories();
        static::indexesBoot();
    }

    public static function loadDirectories() {
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

        $directories = [];
        foreach ($filteredDirs as $dir) {
            $directories[$dir] = $dir;
        }

        return $directories;
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