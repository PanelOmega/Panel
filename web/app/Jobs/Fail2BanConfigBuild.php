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

    public function firewalldBuild()
    {
        $firewalld = new FirewalldBuild();
        $firewalld->handle();
    }

    public function handle(): void
    {
        $settings = setting('fail2ban');
        $pathToJailConfig = '/etc/fail2ban/jail.local';
        $whitelistedIps = $this->getAllWhitlistedIps();
        $fail2BanConf = $this->getJailLocalConf($whitelistedIps, $settings['config']);
        file_put_contents($pathToJailConfig, $fail2BanConf);
        shell_exec('systemctl restart fail2ban');
        $this->firewalldBuild();
    }

    public function getAllWhitlistedIps(): array
    {
        $whitelistedIps = [];
        if (Fail2BanWhitelistedIp::all()) {
            $whitelistedIps = Fail2BanWhitelistedIp::pluck('ip')->toArray();
        }

        return $whitelistedIps;
    }

    public function getJailLocalConf($whitelistedIps, $settings = null)
    {
        $fail2BanConf = view('server.samples.fail2ban.fail2ban_jail_conf', [
            'whitelistedIps' => $whitelistedIps,
            'settings' => $settings
        ])->render();

        return $fail2BanConf;
    }
}
