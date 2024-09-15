<?php

namespace App\Jobs;

use App\Jobs\Traits\VsftpdConfigBuildTrait;
use App\Models\HostingSubscription\FtpAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateVsftpdUserlist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, VsftpdConfigBuildTrait;

    public function handle(): void
    {
        $ftpAccounts = FtpAccount::all();
        $vsftpdFileView = $this->getVsftpdFileConfig($ftpAccounts);
        $vsftpdFilePath = '/etc/vsftpd/user_list';
        $this->updateSystemFile($vsftpdFilePath, $vsftpdFileView);
    }

    public function getVsftpdFileConfig($ftpAccounts) {
        $updateVsfpdUserlist = view('server.samples.vsftpd.vsftpd-userlist-conf', [
            'ftpAccounts' => $ftpAccounts
        ])->render();

        return $updateVsfpdUserlist;
    }
}
