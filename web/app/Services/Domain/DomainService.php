<?php

namespace App\Services\Domain;

use App\Jobs\HtaccessBuildPHPVersions;
use App\Models\Domain;
use App\Server\Helpers\PHP;
use App\Server\VirtualHosts\DTO\ApacheVirtualHostSettings;
use App\Virtualization\Docker\DockerClient;
use Illuminate\Support\Str;

class DomainService
{


    public function configureVirtualHost(int $domainId, $fixPermissions = false, $installSamples = false)
    {
        $domain = \App\Models\Domain::where('id', $domainId)
            ->first();
        if (!$domain) {
            throw new \Exception('Domain not found');
        }

        $findHostingSubscription = \App\Models\HostingSubscription::where('id', $domain->hosting_subscription_id)
            ->first();
        if (!$findHostingSubscription) {
            throw new \Exception('Hosting subscription not found');
        }

        $findHostingPlan = \App\Models\HostingPlan::where('id', $findHostingSubscription->hosting_plan_id)
            ->first();
        if (!$findHostingPlan) {
            throw new \Exception('Hosting plan not found');
        }

        if (empty($domain->domain_root)) {
            throw new \Exception('Domain root not found');
        }

        if ($fixPermissions) {
            $this->fixPermissions($domain->id);
        }

        if ($installSamples) {

            if ($domain->server_application_type == 'apache_php') {
                if (!is_file($domain->domain_public . '/index.php')) {
                    $indexContent = view('server.samples.apache.php.app-php-sample')->render();
                    file_put_contents($domain->domain_public . '/index.php', $indexContent);
                }
                if (!is_dir($domain->domain_public . '/templates')) {
                    mkdir($domain->domain_public . '/templates', 0755, true);
                }
                if (!is_file($domain->domain_public . '/templates/index.html')) {
                    $indexContent = view('server.samples.apache.php.app-index-html')->render();
                    file_put_contents($domain->domain_public . '/templates/index.html', $indexContent);
                }
            }

            if ($domain->server_application_type == 'apache_nodejs') {
                if (!is_file($domain->domain_public . '/app.js')) {
                    $indexContent = view('server.samples.apache.nodejs.app-nodejs-sample')->render();
                    file_put_contents($domain->domain_public . '/app.js', $indexContent);
                }
                if (!is_dir($domain->domain_public . '/templates')) {
                    mkdir($domain->domain_public . '/templates', 0755, true);
                }
                if (!is_file($domain->domain_public . '/templates/index.html')) {
                    $indexContent = view('server.samples.apache.nodejs.app-index-html')->render();
                    file_put_contents($domain->domain_public . '/templates/index.html', $indexContent);
                }
            }

            if ($domain->server_application_type == 'apache_python') {
                if (!is_file($domain->domain_public . '/app.py')) {
                    $indexContent = view('server.samples.apache.python.app-python-sample')->render();
                    file_put_contents($domain->domain_public . '/app.py', $indexContent);
                }
                if (!is_file($domain->domain_public . '/passenger_wsgi.py')) {
                    $indexContent = view('server.samples.apache.python.app-passanger-wsgi-sample')->render();
                    file_put_contents($domain->domain_public . '/passenger_wsgi.py', $indexContent);
                }
                if (!is_dir($domain->domain_public . '/templates')) {
                    mkdir($domain->domain_public . '/templates', 0755, true);
                }
                if (!is_file($domain->domain_public . '/templates/index.html')) {
                    $indexContent = view('server.samples.apache.python.app-index-html')->render();
                    file_put_contents($domain->domain_public . '/templates/index.html', $indexContent);
                }
            }
        }

        $webUser = $findHostingSubscription->system_username;
        $webUserGroup = $findHostingSubscription->system_username;


        $appType = 'php';
        $appVersion = '8.3';

//        if ($domain->server_application_type == 'apache_php') {
//            if (isset($domain->server_application_settings['php_version'])) {
//                $appVersion = $domain->server_application_settings['php_version'];
//            }
//            if (!is_dir($domain->domain_public . '/cgi-bin')) {
//                mkdir($domain->domain_public . '/cgi-bin', 0755, true);
//            }
//            file_put_contents($domain->domain_public . '/cgi-bin/php', '#!/usr/bin/php-cgi' . $appVersion . ' -cphp' . $appVersion . '-cgi.ini');
//            shell_exec('chown '.$findHostingSubscription->system_username.':'.$webUserGroup.' '.$domain->domain_public . '/cgi-bin/php');
//            shell_exec('chmod -f 751 '.$domain->domain_public . '/cgi-bin/php');
//        }

        $apacheVirtualHostBuilder = new ApacheVirtualHostSettings();
        $apacheVirtualHostBuilder->setDomain($domain->domain);
        $apacheVirtualHostBuilder->setDomainPublic($domain->domain_public);
        $apacheVirtualHostBuilder->setDomainRoot($domain->domain_root);
        $apacheVirtualHostBuilder->setHomeRoot($domain->home_root);
        $apacheVirtualHostBuilder->setUser($findHostingSubscription->system_username);
        $apacheVirtualHostBuilder->setUserGroup($webUserGroup);

        if ($domain->status == Domain::STATUS_SUSPENDED) {
            $suspendedPath = '/var/www/html/suspended';
            if (!is_dir($suspendedPath)) {
                mkdir($suspendedPath, 0755, true);
            }
            if (!is_file($suspendedPath . '/index.html')) {
                $suspendedPageHtmlPath = base_path('resources/views/actions/samples/apache/html/app-suspended-page.html');
                file_put_contents($suspendedPath . '/index.html', file_get_contents($suspendedPageHtmlPath));
            }
            $apacheVirtualHostBuilder->setDomainRoot($suspendedPath);
            $apacheVirtualHostBuilder->setDomainPublic($suspendedPath);
        } else if ($domain->status == Domain::STATUS_DEACTIVATED) {
            $deactivatedPath = '/var/www/html/deactivated';
            if (!is_dir($deactivatedPath)) {
                mkdir($deactivatedPath, 0755, true);
            }
            if (!is_file($deactivatedPath . '/index.html')) {
                $deactivatedPageHtmlPath = base_path('resources/views/actions/samples/apache/html/app-deactivated-page.html');
                file_put_contents($deactivatedPath . '/index.html', file_get_contents($deactivatedPageHtmlPath));
            }
            $apacheVirtualHostBuilder->setDomainRoot($deactivatedPath);
            $apacheVirtualHostBuilder->setDomainPublic($deactivatedPath);
        } else if ($domain->status == Domain::STATUS_BROKEN) {
            $brokenPath = '/var/www/html/broken';
            if (!is_dir($brokenPath)) {
                mkdir($brokenPath, 0755, true);
            }
            if (!is_file($brokenPath . '/index.html')) {
                $brokenPageHtmlPath = base_path('resources/views/actions/samples/apache/html/app-broken-page.html');
                file_put_contents($brokenPath . '/index.html', file_get_contents($brokenPageHtmlPath));
            }
            $apacheVirtualHostBuilder->setDomainRoot($brokenPath);
            $apacheVirtualHostBuilder->setDomainPublic($brokenPath);
        } else {

            //  $apacheVirtualHostBuilder->setEnableLogs(true);
            $apacheVirtualHostBuilder->setAdditionalServices($findHostingPlan->additional_services);
            $apacheVirtualHostBuilder->setAppType($appType);
            $apacheVirtualHostBuilder->setAppVersion($appVersion);

            if ($domain->server_application_type == 'apache_php') {

//                if (isset($domain->docker_settings['containerIp'])) {
//                    $apacheVirtualHostBuilder->setFCGI($domain->docker_settings['containerIp'].':9000');
//                }

                if (isset($domain->server_application_settings['enable_php_fpm'])
                    && $domain->server_application_settings['enable_php_fpm'] == true
                ) {

                    $apacheVirtualHostBuilder->setAppType('php_proxy_fcgi');
                    $apacheVirtualHostBuilder->setAppVersion(null);

                    $getCurrentPHPVersion = PHP::getPHPVersion($domain->server_application_settings['php_version']);

                    if (isset($getCurrentPHPVersion['fpmPoolPath'])) {

                        $fcgiPort = $domain->id + 9000;

                        $fpmPoolPath = $getCurrentPHPVersion['fpmPoolPath'];
                        if (is_file($fpmPoolPath . '/www.conf')) {
                            unlink($fpmPoolPath . '/www.conf');
                        }

                        $domainFpmPoolPath = $fpmPoolPath . '/' . $domain->domain . '.conf';

                        $fpmPoolContent = view('server.samples.php-fpm.domain-pool-conf', [
                            'username' => $findHostingSubscription->system_username,
                            'port' => $fcgiPort,
                            'poolName' => $domain->domain
                        ])->render();

                        $restartFpmServices = [];

                        $getSupportedPHPVersions = PHP::getInstalledPHPVersions();
                        // Scan old pool files and remove them
                        $allPoolFiles = shell_exec('find /etc/opt/remi/*/php-fpm.d/' . $domain->domain . '.conf');
                        $allPoolFiles = explode("\n", $allPoolFiles);
                        if (!empty($allPoolFiles)) {
                            foreach ($allPoolFiles as $poolFile) {
                                foreach ($getSupportedPHPVersions as $version) {
                                    if (str_contains($poolFile, $version['fpmPoolPath'])) {
                                        $restartFpmServices[] = $version['fpmServiceName'];
                                        unlink($poolFile);
                                    }
                                }
                            }
                        }

                        file_put_contents($domainFpmPoolPath, $fpmPoolContent);

                        if (isset($getCurrentPHPVersion['fpmServiceName'])) {
                            $restartFpmServices[] = $getCurrentPHPVersion['fpmServiceName'];
                        }
                        if (!empty($restartFpmServices)) {
                            foreach ($restartFpmServices as $service) {
                                shell_exec('systemctl restart ' . $service);
                            }
                        }

                        $apacheVirtualHostBuilder->setFCGI('127.0.0.1:' . $fcgiPort);
                    }
                }
            }

//            if ($domain->server_application_type == 'apache_nodejs') {
//                $apacheVirtualHostBuilder->setAppType('nodejs');
//                $apacheVirtualHostBuilder->setPassengerAppRoot($domain->domain_public);
//                $apacheVirtualHostBuilder->setPassengerAppType('node');
//                $apacheVirtualHostBuilder->setPassengerStartupFile('app.js');
//
//                if (isset($domain->server_application_settings['nodejs_version'])) {
//                    $apacheVirtualHostBuilder->setAppVersion($domain->server_application_settings['nodejs_version']);
//                }
//            }

//            if ($domain->server_application_type == 'apache_python') {
//                $apacheVirtualHostBuilder->setAppType('python');
//                $apacheVirtualHostBuilder->setPassengerAppRoot($domain->domain_public);
//                $apacheVirtualHostBuilder->setPassengerAppType('python');
//
//                if (isset($domain->server_application_settings['python_version'])) {
//                    $apacheVirtualHostBuilder->setAppVersion($domain->server_application_settings['python_version']);
//                }
//            }

//            if ($domain->server_application_type == 'apache_ruby') {
//                $apacheVirtualHostBuilder->setAppType('ruby');
//                $apacheVirtualHostBuilder->setPassengerAppRoot($domain->domain_public);
//                $apacheVirtualHostBuilder->setPassengerAppType('ruby');
//
//                if (isset($domain->server_application_settings['ruby_version'])) {
//                    $apacheVirtualHostBuilder->setAppVersion($domain->server_application_settings['ruby_version']);
//                }
//
//            }
//
        }

        $virtualHostSettings = $apacheVirtualHostBuilder->getSettings();

//        $catchMainDomain = '';
//        $domainExp = explode('.', $domain->domain);
//        if (count($domainExp) > 0) {
//            unset($domainExp[0]);
//            $catchMainDomain = implode('.', $domainExp);
//        }
//
//        $findDomainSSLCertificate = null;
//
//        $findMainDomainSSLCertificate = \App\Models\DomainSslCertificate::where('domain', $domain->domain)
//            ->first();
//        if ($findMainDomainSSLCertificate) {
//            $findDomainSSLCertificate = $findMainDomainSSLCertificate;
//        } else {
//            $findDomainSSLCertificateWildcard = \App\Models\DomainSslCertificate::where('domain', '*.' . $domain->domain)
//                ->where('is_wildcard', 1)
//                ->first();
//            if ($findDomainSSLCertificateWildcard) {
//                $findDomainSSLCertificate = $findDomainSSLCertificateWildcard;
//            } else {
//                $findMainDomainWildcardSSLCertificate = \App\Models\DomainSslCertificate::where('domain', '*.'.$catchMainDomain)
//                    ->first();
//                if ($findMainDomainWildcardSSLCertificate) {
//                    $findDomainSSLCertificate = $findMainDomainWildcardSSLCertificate;
//                }
//            }
//        }
//
//        $virtualHostSettingsWithSSL = null;
//        if ($findDomainSSLCertificate) {
//
//            $sslCertificateFile = $domain->home_root . '/certs/' . $domain->domain . '/public/cert.pem';
//            $sslCertificateKeyFile = $domain->home_root . '/certs/' . $domain->domain . '/private/key.private.pem';
//            $sslCertificateChainFile = $domain->home_root . '/certs/' . $domain->domain . '/public/fullchain.pem';
//
//            if (!empty($findDomainSSLCertificate->certificate)) {
//                if (!file_exists($sslCertificateFile)) {
//                    if (!is_dir($domain->home_root . '/certs/' . $domain->domain . '/public')) {
//                        mkdir($domain->home_root . '/certs/' . $domain->domain . '/public', 0755, true);
//                    }
//                    file_put_contents($sslCertificateFile, $findDomainSSLCertificate->certificate);
//                }
//            }
//
//            if (!empty($findDomainSSLCertificate->private_key)) {
//                if (!file_exists($sslCertificateKeyFile)) {
//                    if (!is_dir($domain->home_root . '/certs/' . $domain->domain . '/private')) {
//                        mkdir($domain->home_root . '/certs/' . $domain->domain . '/private', 0755, true);
//                    }
//                    file_put_contents($sslCertificateKeyFile, $findDomainSSLCertificate->private_key);
//                }
//            }
//
//            if (!empty($findDomainSSLCertificate->certificate_chain)) {
//                if (!file_exists($sslCertificateChainFile)) {
//                    if (!is_dir($domain->home_root . '/certs/' . $domain->domain . '/public')) {
//                        mkdir($domain->home_root . '/certs/' . $domain->domain . '/public', 0755, true);
//                    }
//                    file_put_contents($sslCertificateChainFile, $findDomainSSLCertificate->certificate_chain);
//                }
//            }
//
//            $apacheVirtualHostBuilder->setPort(443);
//            $apacheVirtualHostBuilder->setSSLCertificateFile($sslCertificateFile);
//            $apacheVirtualHostBuilder->setSSLCertificateKeyFile($sslCertificateKeyFile);
//            $apacheVirtualHostBuilder->setSSLCertificateChainFile($sslCertificateChainFile);
//
//            $virtualHostSettingsWithSSL = $apacheVirtualHostBuilder->getSettings();
//
//        }

        if ($fixPermissions) {
            $this->fixPermissions($domain->id);
        }

        return [
            'virtualHostSettings' => $virtualHostSettings,
            //   'virtualHostSettingsWithSSL' => $virtualHostSettingsWithSSL,
        ];

    }

    public function fixPermissions(int $domainId)
    {
        $domain = Domain::where('id', $domainId)
            ->first();
        if (!$domain) {
            throw new \Exception('Domain not found');
        }

        if (empty($domain->domain_root)) {
            throw new \Exception('Domain root not found');
        }

        if (!is_dir($domain->domain_root)) {
            mkdir($domain->domain_root, 0711, true);
        }
        if (!is_dir($domain->domain_public)) {
            mkdir($domain->domain_public, 0755, true);
        }
        if (!is_dir($domain->home_root)) {
            mkdir($domain->home_root, 0711, true);
        }

        $webUser = $domain->hostingSubscription->system_username;
        $webUserGroup = $domain->hostingSubscription->system_username;

        // Fix file permissions
        shell_exec('chown -R ' . $webUser . ':' . $webUserGroup . ' ' . $domain->domain_root);

        shell_exec('chmod -R 0711 ' . $domain->home_root);
        shell_exec('chmod -R 0711 ' . $domain->domain_root);
        shell_exec('chmod -R 775 ' . $domain->domain_public);

        if (!is_dir($domain->domain_root . '/logs/apache2')) {
            shell_exec('mkdir -p ' . $domain->domain_root . '/logs/apache2');
        }
        shell_exec('chown -R ' . $webUser . ':' . $webUserGroup . ' ' . $domain->domain_root . '/logs/apache2');
        shell_exec('chmod -R 775 ' . $domain->domain_root . '/logs/apache2');

        if (!is_file($domain->domain_root . '/logs/apache2/bytes.log')) {
            shell_exec('touch ' . $domain->domain_root . '/logs/apache2/bytes.log');
        }
        if (!is_file($domain->domain_root . '/logs/apache2/access.log')) {
            shell_exec('touch ' . $domain->domain_root . '/logs/apache2/access.log');
        }
        if (!is_file($domain->domain_root . '/logs/apache2/error.log')) {
            shell_exec('touch ' . $domain->domain_root . '/logs/apache2/error.log');
        }

        shell_exec('chmod -R 775 ' . $domain->domain_root . '/logs/apache2/bytes.log');
        shell_exec('chmod -R 775 ' . $domain->domain_root . '/logs/apache2/access.log');
        shell_exec('chmod -R 775 ' . $domain->domain_root . '/logs/apache2/error.log');

    }

    public function configureHtaccess(int $domainId)
    {
        $domain = Domain::where('id', $domainId)
            ->first();
        if (!$domain) {
            throw new \Exception('Domain not found');
        }

        if ($domain->server_application_type !== 'apache_php') {
            return;
        }
        if (!isset($domain->server_application_settings['php_version'])) {
            return;
        }
        $phpVersion = PHP::getPHPVersion($domain->server_application_settings['php_version']);
        if (!$phpVersion) {
            return;
        }

        $htaccessBuild = new HtaccessBuildPHPVersions(false, $domain->hosting_subscription_id, $phpVersion);
        $htaccessBuild->handle();
    }


    public function createDockerContainer()
    {
        $dockerClient = new DockerClient();

        // $containers = $dockerClient->listContainers()['response'];
        // dd($containers);
//        foreach ($containers as $container) {
//            $dockerClient->stopContainer($container['Id']);
//            $dockerClient->deleteContainer($container['Id']);
//        }
        //return;

        if ($domain->server_application_type !== 'apache_php') {
            return;
        }
        return;

        $dockerImage = 'php:8.2-fpm';

        if (isset($domain->server_application_settings['php_version'])) {
            $dockerImage = 'php:' . $domain->server_application_settings['php_version'] . '-fpm';
        }

        $dockerClient->pullImage($dockerImage);
        $dockerContainerName = Str::slug($dockerImage . $domain->domain);
        $createDocker = $dockerClient->createContainer($dockerContainerName, [
            'Image' => $dockerImage,
            'HostConfig' => [
                'Binds' => [
                    $domain->domain_public . ':' . $domain->domain_public
                ]
            ]
        ]);

        if (!isset($createDocker['response']['Id'])) {
            throw new \Exception('Docker container not created. Error: ' . json_encode($createDocker) ?? 'Unknown error');
        }

        $startDockerContainer = $dockerClient->startContainer($createDocker['response']['Id']);
        $getDockerContainer = $dockerClient->getContainer($createDocker['response']['Id']);
        $dockerContainerIp = $getDockerContainer['response']['NetworkSettings']['Networks']['bridge']['IPAddress'];

        $domain->docker_settings = [
            'containerId' => $createDocker['response']['Id'],
            'containerName' => $dockerContainerName,
            'containerIp' => $dockerContainerIp
        ];
        $domain->saveQuietly();
    }

}
