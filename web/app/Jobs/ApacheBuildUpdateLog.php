<?php

namespace App\Jobs;

use App\Models\Domain;
use App\Server\Helpers\OS;
use App\Server\Helpers\PHP;
use App\Services\Domain\DomainService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApacheBuildUpdateLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;
    public $domains;

    public function __construct($fixPermissions = false, $domains)
    {
        $this->fixPermissions = $fixPermissions;
        $this->domains = $domains;
    }

    public function handle() {

        $virtualHosts = [];
        $domainService = new DomainService();

        foreach ($this->domains as $domain) {
            try {
                $virtualHostSettings = $domainService->configureVirtualHost($domain->id);
                if (isset($virtualHostSettings['virtualHostSettings'])) {
                    $virtualHosts[] = $virtualHostSettings['virtualHostSettings'];
                }
                if (isset($virtualHostSettings['virtualHostSettingsWithSSL'])) {
                    $virtualHosts[] = $virtualHostSettings['virtualHostSettingsWithSSL'];
                }
            } catch (\Exception $e) {
                //     echo $e->getMessage();
            }
        }

        $os = OS::getDistro();

        // Build Apache2 configuration
        $apache2 = view('server.samples.configs.apache2-conf-build', [
            'installedPHPVersions' => PHP::getInstalledPHPVersions(),
            'virtualHosts' => $virtualHosts,
            'os' => $os,
        ])->render();

        $apache2 = preg_replace('~(*ANY)\A\s*\R|\s*(?!\r\n)\s$~mu', '', $apache2);

        if ($os == OS::UBUNTU || $os == OS::DEBIAN) {
            file_put_contents('/etc/my-apache/apache2.conf', $apache2);
            shell_exec('systemctl reload apache2');
        } if ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {

            file_put_contents('/etc/my-apache/conf/httpd.conf', $apache2);
            shell_exec('systemctl reload httpd');

        } else {
            throw new \Exception('Unsupported OS');
        }
    }
}
