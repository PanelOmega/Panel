<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\HostingSubscriptionFtpAccount;

class UpdateVsftpdUserlist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {

        try {

            $ftpAccounts = HostingSubscriptionFtpAccount::all();

            $updateVsfpdUserlist = view('server.samples.vsftpd.vsftpd-userlist-conf', [
                'ftpAccounts' => $ftpAccounts
            ])->render();

            $updateVsfpdUserlist = preg_replace('/^\s+|\s+$/m', '', $updateVsfpdUserlist);

            $save = file_put_contents('/etc/vsftpd/user_list', $updateVsfpdUserlist);
            if (!$save) {
                throw new \Exception("Failed to update vsftpd.userlist");
            }

//            echo "vsftpd.userlist updated successfully.";

        } catch (\Exception $e) {
//            echo "Failed to update vsftpd.userlist: " . $e->getMessage();

        }
    }
}
