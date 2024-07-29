<?php

namespace App\Jobs;

use App\Models\Fail2BanWhitelistedIp;
use App\Server\Helpers\OS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Fail2BanConfigBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;

    public function __construct($fixPermissions = false)
    {
        $this->fixPermissions = $fixPermissions;
    }

    public function handle(): void
    {
        $os = OS::getDistro();

        $settings = setting('fail2ban');

        $whitelistedIps = [];
        if (Fail2BanWhitelistedIp::all()) {
            $whitelistedIps = Fail2BanWhitelistedIp::pluck('ip')->toArray();
        }

        $fail2BanConf = view('server.samples.fail2ban.fail2ban_jail_conf', [
            'whitelistedIps' => $whitelistedIps,
            'settings' => $settings['config'] ?? null
        ])->render();

        if ($os == OS::UBUNTU || $os == OS::DEBIAN) {

        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            file_put_contents('/etc/fail2ban/jail.local', $fail2BanConf);
            shell_exec('systemctl restart fail2ban');
        } else {
            throw new \Exception('Unsupported OS');
        }

    }
}
