<?php

namespace App\Models\HostingSubscription;

use App\Models\Customer;
use App\Models\HostingSubscription;
use App\Models\Scopes\CustomerHostingSubscriptionScope;
use App\Models\Scopes\HostingSubscriptionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;

class GitSshKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'name',
        'private_key',
        'public_key',
    ];

    protected $table = 'hosting_subscription_git_ssh_keys';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CustomerHostingSubscriptionScope());
    }

    public static function boot(): void
    {
        parent::boot();
//        static::gitSshKeyBoot();
    }

//    public static function gitSshKeyBoot()
//    {
//        static::creating(function ($model) {
//
//            $keyName = 'id_rsa';
//            $model->name = $keyName;
//
//            $tempPrivateKeyPath = sys_get_temp_dir() . '/' . $keyName;
//            $tempPublicKeyPath = $tempPrivateKeyPath . '.pub';
//
//            $command = "ssh-keygen -t rsa -b 2048 -f $tempPrivateKeyPath -N ''";
//            shell_exec($command);
//
//            $privateKey = file_get_contents($tempPrivateKeyPath);
//            $publicKey = file_get_contents($tempPublicKeyPath);
//
//            $model->private_key = $privateKey;
//            $model->public_key = $publicKey;
//
//            unlink($tempPrivateKeyPath);
//            unlink($tempPublicKeyPath);
//
////                $private = RSA::createKey();
////                $public = $private->getPublicKey();
////
////                $privateKeyString = $private->toString('OpenSSH');
////                $publicKeyString = $public->toString('OpenSSH');
////
////                $model->private_key = $privateKeyString;
////                $model->public_key = $publicKeyString;
//
//
//        });
//    }

//    public function hostingSubscription()
//    {
//        return $this->belongsTo(HostingSubscription::class);
//    }

//    public static function getKeyId(string $hostingSubscriptionId)
//    {
//        if (GitSshKey::where('hosting_subscription_id', $hostingSubscriptionId)->exists()) {
//            return GitSshKey::where('hosting_subscription_id', $hostingSubscriptionId)->value('id');
//        }
//        return null;
//    }
}
