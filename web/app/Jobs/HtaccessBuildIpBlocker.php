<?php

namespace App\Jobs;

use App\Jobs\Traits\ErrorCodeDefaultContentTrait;
use App\Jobs\Traits\HtaccessBuildTrait;
use App\Models\HostingSubscription\IpBlocker;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildIpBlocker
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait, ErrorCodeDefaultContentTrait;

    public $fixPermissions = false;
    public $ipBlockerPath;
    public $hostingSubscriptionId;
    public $startComment = '# Section managed by Panel Omega: Ip Blocker, do not edit';
    public $endComment = '# End section managed by Panel Omega: Ip Blocker';

    public function __construct($fixPermissions = false, $ipBlockerPath, $hostingSubscriptionId)
    {
        $this->fixPermissions = $fixPermissions;
        $this->ipBlockerPath = $ipBlockerPath;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
    }

    public function handle()
    {
        $blockedIps = $this->getAllBlockedIps();
        $htaccessView = $this->getHtaccessIpBlockerConfig($blockedIps);
        $this->updateSystemFile($this->ipBlockerPath, $htaccessView);
    }

    public function getAllBlockedIps(): array
    {
        $blockedIps = [];
        $ips = IpBlocker::where('hosting_subscription_id', $this->hostingSubscriptionId)->pluck('blocked_ip');
        foreach ($ips as $ip) {
            $blockedIps[] = "Deny from {$ip}";
        }
        return $blockedIps;
    }

    public function getHtaccessIpBlockerConfig($blockedIps)
    {
        $htaccessIpBlockerContent = view('server.samples.apache.php.ip-blockers-htaccess', [
            'blockedIps' => $blockedIps,
        ])->render();

        $htaccessIpBlockerContent = preg_replace_callback(
            '/^.*$/m',
            function ($matches) {
                return preg_replace('/\s+/', ' ', trim($matches[0]));
            },
            $htaccessIpBlockerContent
        );
        $htaccessIpBlockerContent = preg_replace('/^\s*[\r\n]/m', '', $htaccessIpBlockerContent);
        return $htaccessIpBlockerContent;
    }
}
