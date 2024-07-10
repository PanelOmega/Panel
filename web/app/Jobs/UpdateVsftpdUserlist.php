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

    public static $handled;

    public function handle(): void
    {
        $ftpAccounts = HostingSubscriptionFtpAccount::all();
        $filePath = '/etc/vsftpd.userlist';
        $tempFilePath = $filePath . '.tmp';

        try {
            $updateVsfpdUserlist = view('server.samples.vsdftpd.vsftpd-userlist-conf', [
                'ftpAccounts' => $ftpAccounts
            ])->render();

            $updateVsfpdUserlist = preg_replace('/^\s+|\s+$/m', '', $updateVsfpdUserlist);

            file_put_contents($tempFilePath, $updateVsfpdUserlist);

            if (!rename($tempFilePath, $filePath)) {
                throw new \Exception("Failed to update vsftpd.userlist");
            }

            echo "vsftpd.userlist updated successfully.";
            self::$handled = true;

        } catch (\Exception $e) {
            echo "Failed to update vsftpd.userlist: " . $e->getMessage();
            self::$handled = false;
        }
    }
}
