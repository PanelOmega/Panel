<?php

namespace App\Models\HostingSubscription;

use App\Jobs\UpdateVsftpdUserlist;
use App\Models\Customer;
use App\Models\HostingSubscription;
use App\Server\Helpers\LinuxUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FtpAccount extends Model
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

    protected $table = 'hosting_subscription_ftp_accounts';

    public static function boot()
    {
        parent::boot();
        static::ftpAccountBoot();
    }

    public static function ftpAccountBoot()
    {
        static::creating(function ($model) {

            $create = $model->_createFtpAccount();

            if (isset($create['error'])) {
                throw new \Exception($create['message']);
            }

            $model->ftp_username = $create['ftp_username'];
            $model->ftp_username_prefix = $create['ftp_username_prefix'];
            $model->hosting_subscription_id = $create['hosting_subscription_id'];

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

            $deleteFtpAccount = $model->_deleteFtpAccount();

            if (isset($deleteFtpAccount['error'])) {
                throw new \Exception($deleteFtpAccount['message']);
            }

            $updateFtpUsers = new UpdateVsftpdUserlist();
            $updateFtpUsers->handle();
        });
    }

    /**
     * @param
     * @return array
     */
    private function _createFtpAccount(): array
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        if (empty($hostingSubscription)) {
            return [
                'error' => true,
                'message' => 'Hosting subscription not found.'
            ];
        }

        $checkFtpUser = $this->_getFtpAccountByUsername($this->ftp_username);
        if (!empty($checkFtpUser)) {
            return [
                'error' => true,
                'message' => 'Ftp account already exists.'
            ];
        }

        $hostingSubscriptionId = $hostingSubscription->id;
        $ftpUsername = Str::slug($this->ftp_username, '_');
        $ftpUsernamePrefix = $hostingSubscription->system_username . '_';

        $ftpUsernameWithPrefix = $ftpUsernamePrefix . $ftpUsername;
        $rootPath = "/home/$hostingSubscription->system_username";
        if (!empty($this->ftp_path)) {
            $rootPath .= '/' . $this->ftp_path;
        }

        $createLinuxUser = LinuxUser::createUser(
            $ftpUsernameWithPrefix,
            $this->ftp_password,
            $hostingSubscription->customer->email,
            [
                'homeDir' => $rootPath,
//                'noLogin' => true,
            ]
        );
        if (!isset($createLinuxUser['success'])) {
            return [
                'error' => true,
                'message' => 'Failed to create user on the system.',
            ];
        }

        $commands = [
            "sudo usermod -d $rootPath $ftpUsernameWithPrefix",
            "sudo usermod -a -G $hostingSubscription->system_username $ftpUsernameWithPrefix",
            "sudo usermod -a -G $hostingSubscription->system_username $hostingSubscription->system_username",
            "sudo chgrp -R $hostingSubscription->system_username $rootPath",
            "sudo chmod -R 770 $rootPath",
        ];


        $output = '';
        foreach ($commands as $command) {
            $output .= shell_exec($command);
        }

        return [
            'success' => true,
            'message' => 'Ftp account has been created.',
            'hosting_subscription_id' => $hostingSubscriptionId,
            'ftp_username' => $ftpUsername,
            'ftp_username_prefix' => $ftpUsernamePrefix,
        ];

    }

    /**
     * @param string $username
     * @return string[]|null
     */
    private function _getFtpAccountByUsername(string $username)
    {
        $accountData = FtpAccount::where('ftp_username', $username)->first();
        if ($accountData) {
            return $accountData;
        }

        return null;

    }

    /**
     * @param string $username
     * @return array
     */
    private function _deleteFtpAccount(): array
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

    public function hostingSubscription()
    {
        return $this->belongsTo(HostingSubscription::class);
    }

    /**
     * @param
     * @return string
     */
    public function getFtpQuotaTextAttribute(): string
    {

        return $this->ftp_quota ?? $this->ftp_quota_type;
    }

    public function getFtpPathTextAttribute()
    {
        $mainPath = '/home/' . $this->hostingSubscription->system_username;
        if (!empty($this->ftp_path)) {
            $mainPath .= '/' . $this->ftp_path;
        }
        return $mainPath;
    }

    public function getFtpPortTextAttribute()
    {
        return '21';
    }

    public function getFtpHostTextAttribute()
    {
        return 'ftp.' . $this->domain;
    }

    /**
     * @param
     * @return string
     */
    public function getFtpUsernameWithPrefixAttribute(): string
    {
        $username = $this->ftp_username_prefix . $this->ftp_username;
        return $username;
    }

}
