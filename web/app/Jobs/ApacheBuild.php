<?php

namespace App\Jobs;

use App\MasterDomain;
use App\Models\Domain;
use App\Server\Helpers\OS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApacheBuild implements ShouldQueue
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
        $getAllDomains = Domain::whereNot('status','<=>', 'broken')->get();
        $virtualHosts = [];
        foreach ($getAllDomains as $domain) {
            try {
                $virtualHostSettings = $domain->configureVirtualHost();
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

        $apache2 = view('server.samples.configs.apache2-conf-build', [
            'virtualHosts' => $virtualHosts,
            'os' => $os,
        ])->render();

        $apache2 = preg_replace('~(*ANY)\A\s*\R|\s*(?!\r\n)\s$~mu', '', $apache2);

        if ($os == OS::UBUNTU || $os == OS::DEBIAN) {
            file_put_contents('/etc/apache2/apache2.conf', $apache2);
            shell_exec('systemctl reload apache2');
        } if ($os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            file_put_contents('/etc/httpd/conf/httpd.conf', $apache2);
            shell_exec('systemctl reload httpd');
        }

    }
}
