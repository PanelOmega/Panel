<?php

namespace App\Models;

use App\Server\Helpers\FtpAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\UpdateVsftpdUserlist;
use Illuminate\Support\Str;

class HostingSubscriptionFtpAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'domain',
        'ftp_username',
        'ftp_username_prefix',
        'ftp_password',
        'ftp_path',
        'ftp_quota',
        'ftp_quota_type',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('customer', function (Builder $query) {
            if (auth()->check() && auth()->guard()->name == 'customer') {
                $query->whereHas('hostingSubscription', function ($query) {
                    $query->where('customer_id', auth()->user()->id);
                });
            }
        });
    }
    
    public static function boot()
    {

        parent::boot();

        static::creating(function ($model) {

            $hostingSubscription = HostingSubscription::where('domain', $model->domain)->first();

            if (empty($hostingSubscription)) {
                return [
                    'error' => true,
                    'message' => 'Hosting subscription not found.'
                ];
            }
            $model->hosting_subscription_id = $hostingSubscription->id;

            $model->ftp_username = Str::slug($model->ftp_username, '_');
            $model->ftp_username_prefix = $hostingSubscription->system_username . '_';

            $create = $model->createFtpAccount();

            if (isset($create['error'])) {
                throw new \Exception($create['message']);
            }

        });

        static::created(function ($model) {
            $updateFtpUsers = new UpdateVsftpdUserlist();
            $updateFtpUsers->handle();
        });

        static::updated(function ($model) {

            $updateFtpUsers = new UpdateVsftpdUserlist();
            $updateFtpUsers->handle();
        });

        static::deleting(function ($model) {

            $deleteFtpAccount = $model->deleteFtpAccount();

            if (isset($deleteFtpAccount['error'])) {
                throw new \Exception($deleteFtpAccount['message']);
            }

            $updateFtpUsers = new UpdateVsftpdUserlist();
            $updateFtpUsers->handle();
        });


    }

    public function hostingSubscription()
    {
        return $this->belongsTo(HostingSubscription::class);
    }

    /**
     * @param
     * @return array
     */
    public function createFtpAccount(): array
    {

        if (!$this->checkFtpConnection()) {
            return [
                'error' => true,
                'message' => 'Failed to start FTP server.'
            ];
        }

        $checkFtpUser = $this->getFtpAccount($this->ftp_username);

        if (!empty($checkFtpUser)) {
            return [
                'error' => true,
                'message' => 'Ftp account already exists.'
            ];
        }
        $ftpUsername = $this->ftp_username_prefix . $this->ftp_username;
        $this->ftp_password = md5(uniqid($this->ftp_password, true));
        $ftpPassword = $this->ftp_password;
        $rootPath = "/home/{$this->ftp_username_prefix}/{$this->ftp_path}";

        $commands = [
            "sudo useradd {$ftpUsername}",
            "echo '{$ftpUsername}:{$ftpPassword}' | sudo chpasswd",
            "sudo mkdir -p {$rootPath}",
            "sudo chown -R {$ftpUsername}: {$rootPath}",
        ];

        foreach ($commands as $command) {
            shell_exec($command);
        }

        return [
            'success' => true,
            'message' => 'Ftp account has been created.'
        ];

    }

    /**
     * @param string $username
     * @return string[]|null
     */
    public function getFtpAccount(string $username)
    {

        $accountData = HostingSubscriptionFtpAccount::where('ftp_username', $username)->first();
        if ($accountData) {
            return $accountData;
        }

        return null;

    }

    /**
     * @param string $username
     * @return array
     */
    public function deleteFtpAccount(): array
    {

        $ftpUsername = strtolower($this->ftp_username_prefix . $this->ftp_username);

        $command = "sudo userdel " . $ftpUsername;
        shell_exec($command);

        $checkDeleted = shell_exec('id ' . $ftpUsername);

        if ($checkDeleted !== null) {
            return [
                'error' => true,
                'message' => 'Failed to delete user from the system.',
            ];
        }

        return [
            'success' => 'User deleted successfully',
        ];
    }

    /**
     * @param
     * @return bool
     */
    public function checkFtpConnection(): bool
    {

        $isFtpServerActive = function () {
            return trim(shell_exec('sudo systemctl is-active vsftpd')) === 'active';
        };

        if (!$isFtpServerActive()) {
            shell_exec('sudo systemctl start vsftpd');
        }

        return $isFtpServerActive();
    }

    /**
     * @param
     * @return string
     */
    public function getFtpQuotaTextAttribute(): string
    {

        return $this->ftp_quota ?? $this->ftp_quota_type;
    }

    /**
     * @param
     * @return string
     */
    public function getFtpNameWithPrefixAttribute(): string
    {

        $username = $this->ftp_username_prefix . $this->ftp_username;
        return $username;
    }

}
