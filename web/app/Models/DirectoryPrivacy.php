<?php

namespace App\Models;

use App\Services\HtDocBuild\HtDocBuildService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

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

    public static function boot() {
        parent::boot();

        static::creating(function ($model) {

            $domain = Domain::where('status', Domain::STATUS_ACTIVE)->first();
            if($domain) {
                $model->hosting_subscription_id = $domain->hosting_subscription_id;
                $directory = str_replace($domain->domain_root, '', $model->directory);
                $directory == '' ? $model->directory = '/' : $model->directory = $directory;

                self::setDirectory($model, $domain);


            } else {
                throw new \Exception("Domain not found");
            }
        });

        static::updating(function ($model) {
            $domain = Domain::where('status', Domain::STATUS_ACTIVE)->first();
            if($domain) {
                self::setDirectory($model, $domain);

            } else {
                throw new \Exception("Domain not found");
            }
        });

        static::deleting(function ($model) {
            $domain = Domain::where('status', Domain::STATUS_ACTIVE)->first();
            if($domain) {
                self::deleteFromDirectory($model->username, $domain);
            } else {
                throw new \Exception("Domain not found");
            }
        });
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

    public static function setDirectory(DirectoryPrivacy $model, Domain $domain): void
    {
        $username = $model->username;
        $pasword = $model->password;
        $label = $model->label ?? 'Restricted Directory';
        $hashedPasword = password_hash($pasword, PASSWORD_DEFAULT);

        $htPasswdContent = [
            'username' => $username,
            'password' => $hashedPasword,
        ];

        HtDocBuildService::buildHtpasswdByDomain($domain, $htPasswdContent);

        $htAccessContent = [
            'auth_type' => 'Basic',
            'auth_name' => $label,
            'auth_user_file' => $domain->domain_public,
            'require' => 'valid_user'
        ];

        HtDocBuildService::buildHtaccessByDomain($domain, $htAccessContent);
    }

    public static function deleteFromDirectory(string $username, Domain $domain): void
    {
        $dPrivacyContent = [
            'username' => $username,
        ];

        HtDocBuildService::deleteFromHtpasswdByDomain($domain, $dPrivacyContent);

    }
}
