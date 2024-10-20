<?php

namespace App\Jobs;

use App\Server\Helpers\OS;
use App\Server\Helpers\PHP;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DomainPHPFPMBuild implements ShouldQueue
{
    use Queueable;

    public $domains = [];

    /**
     * Create a new job instance.
     */
    public function __construct($domains)
    {
        $this->domains = $domains;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fpms = [];
        foreach ($this->domains as $domain) {
            if ($domain->server_application_type == 'apache_php') {
                if (isset($domain->server_application_settings['php_version'])) {
                    $phpVersion = $domain->server_application_settings['php_version'];
                    $fpms[$phpVersion][] = $domain;
                }
            }
        }

        $os = OS::getDistro();

        // Build PHP FPM configuration
        $phpVersions = PHP::getInstalledPHPVersions();
        if (!empty($phpVersions)) {

            $restartFPMServices = [];
            foreach ($fpms as $phpVersion=>$domainsFPM) {
                $getCurrentPHPVersion = PHP::getPHPVersion($phpVersion);
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

            // Stop FPMS that are not in use
            foreach ($phpVersions as $phpVersion) {
                if (!isset($fpms[$phpVersion['full']])) {
                    echo 'FPM with version '.$phpVersion['full'].' is not in use. Stopping service' . PHP_EOL;
                    if (isset($phpVersion['fpmServiceName'])) {
                        shell_exec('systemctl stop '.$phpVersion['fpmServiceName']);
                    }
                }
            }

            if (!empty($restartFPMServices)) {
                foreach ($restartFPMServices as $service) {
                    shell_exec('systemctl restart '.$service);
                }
            }
        }

    }
}
