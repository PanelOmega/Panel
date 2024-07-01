<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Server\Installers\FtpServers\FtpServerInstaller;

class HostingSubscriptionFtpAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'ftp_username',
        'ftp_password',
        'domain',
        'status',
    ];

    public function hostingSubscription()
    {
        return $this->belongsTo(HostingSubscription::class);
    }

    protected static function booted(): void
    {

        $ftpServerStatus = FtpServerInstaller::isFtpServerInstalled();

        if ($ftpServerStatus['status'] === 'error') {

            $ftpInstaller = new FtpServerInstaller();
            $ftpInstaller->run();

        } else {

            $ftpServerRunning = trim(shell_exec('sudo systemctl is-active vsftpd'));
            if ($ftpServerRunning !== 'active') {
                shell_exec('sudo systemctl start vsftpd');
            }

        }
    }

}