<?php

namespace App\Jobs;

use App\Models\Fail2BanWhitelistedIp;
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
        $settings = setting('fail2ban');
        $pathToConfig = '/etc/fail2ban/jail.local';

        $whitelistedIps = [];
        if (Fail2BanWhitelistedIp::all()) {
            $whitelistedIps = Fail2BanWhitelistedIp::pluck('ip')->toArray();
        }

        $fail2BanConf = view('server.samples.fail2ban.fail2ban_jail_conf', [
            'whitelistedIps' => $whitelistedIps,
            'settings' => $settings['config'] ?? null
        ])->render();

        file_put_contents($pathToConfig, $fail2BanConf);
        shell_exec('systemctl restart fail2ban');
    }
}
