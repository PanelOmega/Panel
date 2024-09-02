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

class WebServerBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;

    /**
     * Create a new job instance.
     */
    public function __construct($fixPermissions = false)
    {
        $this->fixPermissions = $fixPermissions;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $virtualHosts = [];
        $domainsFPMs = [];

        $domainService = new DomainService();
        $getAllDomains = Domain::whereNot('status','<=>', 'broken')->get();


        Bus::chain([
            new DomainPHPFPMBuild($getAllDomains),
            new WebServerBuild($getAllDomains)
        ])->dispatch();


        dd(4);
        foreach ($getAllDomains as $domain) {
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
            if ($domain->server_application_type == 'apache_php') {
                if (isset($domain->server_application_settings['php_version'])) {
                    $phpVersion = $domain->server_application_settings['php_version'];
                    $domainsFPMs[$phpVersion][] = $domain;
                }
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

        // Build PHP FPM configuration
        $phpVersions = PHP::getInstalledPHPVersions();
        if (empty($domainsFPMs)) {
            // No PHP FPMs
        } else {
            $restartFPMServices = [];
            foreach ($domainsFPMs as $domainPHPVersion=>$domainsFPM) {
                $getCurrentPHPVersion = PHP::getPHPVersion($domainPHPVersion);
                if (isset($getCurrentPHPVersion['fpmConfRealpath'])) {

                    $fpmDomainsSettings = [];
                    foreach ($domainsFPM as $domainFPM) {
                        $fcgiPort = $domainFPM->id + 9000;
                        $fpmDomainsSettings[] = [
                            'fcgiPort' => $fcgiPort,
                            'poolName' => $domainFPM->domain,
                            'username' => $domainFPM->hostingSubscription->system_username,
                        ];
                    }

                    $fpmConfigContent = view('server.samples.php-fpm.php-fpm-conf', [
                        'os' => $os,
                        'phpVersion'=> [
                            'shortWithDot' => $getCurrentPHPVersion['shortWithoutDot'],
                        ],
                        'domains' => $fpmDomainsSettings,
                    ])->render();

                    file_put_contents($getCurrentPHPVersion['fpmConfRealpath'], $fpmConfigContent);
                    $restartFPMServices[] = $getCurrentPHPVersion['fpmServiceName'];
                }
            }
            if (!empty($restartFPMServices)) {
                foreach ($restartFPMServices as $service) {
                    shell_exec('systemctl restart '.$service);
                }
            }
        }

        // Build Apache2 configuration
        $apache2 = view('server.samples.configs.apache2-conf-build', [
            'installedPHPVersions' => PHP::getInstalledPHPVersions(),
            'virtualHosts' => $virtualHosts,
            'os' => $os,
        ])->render();

        $apache2 = preg_replace('~(*ANY)\A\s*\R|\s*(?!\r\n)\s$~mu', '', $apache2);

        if ($os == OS::UBUNTU || $os == OS::DEBIAN) {
            file_put_contents('/etc/apache2/apache2.conf', $apache2);
            shell_exec('systemctl reload apache2');
        } if ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            file_put_contents('/etc/httpd/conf/httpd.conf', $apache2);
            shell_exec('systemctl reload httpd');
        } else {
            throw new \Exception('Unsupported OS');
        }

    }
}
