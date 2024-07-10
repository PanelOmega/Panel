<?php

namespace App\Models;

use App\Server\Helpers\FtpAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\UpdateVsftpdUserlist;
class HostingSubscriptionFtpAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'ftp_username',
        'ftp_password',
        'ftp_path',
        'ftp_quota',
        'ftp_quota_unlimited',
    ];

    public function hostingSubscription()
    {
        return $this->belongsTo(HostingSubscription::class);
    }

    public static function boot() {

        parent::boot();

        static::created(function ($model) {

            $createFtpAccount = FtpAccount::createFtpAccount($model);

            if(isset($createFtpAccount['error'])) {
                throw new \Exception($createFtpAccount['message']);
            }

        });

        static::deleting(function ($model) {

            $deleteFtpAccount = FtpAccount::deleteFtpAccount($model->ftp_username);

            if(isset($deleteFtpAccount['error'])) {
                throw new \Exception($deleteFtpAccount['message']);
            }

            $updateFtpUsers = new UpdateVsftpdUserlist();
            $updateFtpUsers->handle();

        });

    }

}
