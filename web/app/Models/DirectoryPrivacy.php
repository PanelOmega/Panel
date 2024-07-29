<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class DirectoryPrivacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'directory',
        'allowed_username',
        'allowed_password',
        'protected',
        'label'
    ];

    public static function boot() {
        parent::boot();

        static::creating(function ($model) {
self::scanDirectories();
        });

        static::updating(function ($model) {

        });

        static::deleting(function ($model) {

        });
    }

    public static function scanDirectories() {

        $customer = Customer::getHostingSubscriptionSession();
        $username = $customer['system_username'];
        $baseDir = '/home/' . $username;
        $directories = File::directories($baseDir);

        dd($directories);

        foreach($directories as $directory) {

        }
    }
}
