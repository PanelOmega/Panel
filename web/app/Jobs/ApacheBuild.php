<?php

namespace App\Jobs;

use App\MasterDomain;
use App\Models\Domain;
use App\Server\Helpers\OS;
use App\Server\Helpers\PHP;
use App\Services\Domain\DomainService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class ApacheBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;
    public $domains = [];

    /**
     * Create a new job instance.
     */
    public function __construct($domains, $fixPermissions = false)
    {
        $this->domains = $domains;
        $this->fixPermissions = $fixPermissions;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

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

        // Make master domain virtual host
//        if (!empty(setting('general.master_domain'))) {
//            // Make master domain virtual host
//            $masterDomain = new MasterDomain();
//            $domainVirtualHost = $masterDomain->configureVirtualHost();
//            if (isset($domainVirtualHost['virtualHostSettings'])) {
//                $virtualHosts[] = $domainVirtualHost['virtualHostSettings'];
//            }
//            if (isset($domainVirtualHost['virtualHostSettingsWithSSL'])) {
//                $virtualHosts[] = $domainVirtualHost['virtualHostSettingsWithSSL'];
//            }
//        }

        // Make wildcard domain virtual host
//        $wildcardDomain = setting('general.wildcard_domain');
//        if (!empty($wildcardDomain)) {
//            // Make wildcard domain virtual host
//            $masterDomain = new MasterDomain();
//            $masterDomain->domain = $wildcardDomain;
//            $domainVirtualHost = $masterDomain->configureVirtualHost();
//            if (isset($domainVirtualHost['virtualHostSettings'])) {
//                $virtualHosts[] = $domainVirtualHost['virtualHostSettings'];
//            }
//            if (isset($domainVirtualHost['virtualHostSettingsWithSSL'])) {
//                $virtualHosts[] = $domainVirtualHost['virtualHostSettingsWithSSL'];
//            }
//        }

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
