<?php

namespace App\Jobs;

namespace App\Jobs;

use App\Models\HostingSubscription\ZoneEditor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ZoneEditorConfigBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;

    public $hostingSubscription;

    public $ip;

    public function __construct($fixPermissions = false, $hostingSubscription = null, $ip = null)
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscription = $hostingSubscription;
        $this->ip = $ip;
    }

    public function handle()
    {
        $service = $this->checkService();

        $currentDomains = $this->getCurrentDomains();
        $this->udpateConfigs($currentDomains);

        foreach ($currentDomains as $domain) {
            $recordsData = $this->getZonesData($domain);
            $this->updateZoneForwardConfig($recordsData, $domain);
            if ($this->ip !== null) {
                $this->updateZoneReverseConfig($recordsData, $this->ip, $domain);
            }
        }
        $service !== 'none' ? shell_exec("systemctl restart {$service}") : null;
    }

    public function udpateConfigs(array $currentDomains)
    {
        $confPath = '/etc/named.conf';

        $server = $this->getNsIp();

        $allowQParts = explode('.', $server);
        $allowQueryIp = implode('.', array_slice($allowQParts, 0, 3));
//        $trustedIps = $this->getDomainTrustedIps() ?? '';

        $zonesData = $this->getZones($currentDomains);

        $bind9ConfigData = [
//            'aclTrusted' => $trustedIps,
//            'portV4Ips' => 'trusted;',
            'allowQuery' => "localhost; {$allowQueryIp}.0/24;",
            'forwarders' => [
                '8.8.8.8',
                '8.8.4.4'
            ],
            'dnsValidation' => 'auto',
//            'service' => $service,
            'forwardZones' => $zonesData['forwardData'],
            'reverseZones' => $zonesData['reverseData'],
        ];

        $bind9Config = view('server.samples.bind9.bind9_named_conf', [
            'bind9Data' => $bind9ConfigData
        ])->render();

        file_put_contents($confPath, $bind9Config);
//        $service === 'pdns' ? $this->addZonesToPdns() : null;
        $this->updateResolv();
    }

    public function getZones(array $currentDomains)
    {

        $revIpData = [
            $this->getNsIp(),
            $this->ip
        ];

        $forwardZonesData = [];
        foreach ($currentDomains as $domain) {
            $forwardZonesData[] = [
                'domain' => $domain
            ];
        }

//        $revIps = $this->getRevIps($revIpData);

        $reverseZonesData = [];
//        foreach($revIps as $ip) {
//            $reverseZonesData[] = [
//                'ip' => $ip
//            ];
//        }

        return [
            'forwardData' => $forwardZonesData,
            'reverseData' => $reverseZonesData
        ];
    }

    public function getZonesData($domain)
    {
        $ttl = 14400;
        $serial = now()->format('Ymd') . '01';
        $refresh = 30800;
        $retry = 1800;
        $expire = 1209600;
        $negativeCache = 86400;
        $zones = ZoneEditor::where('domain', $domain)
//            ->where('hosting_subscription_id', $this->hostingSubscription->id)
            ->get()
            ->toArray();

        $nsParts = explode('.', setting('general.ns1'));
        array_shift($nsParts);
        $adminNs = implode('.', $nsParts);

        $server = $this->getNsIp();

        return [
            'ttl' => $ttl,
            'domain' => $domain,
            'nsNames' => [
                setting('general.ns1') ?? null,
                setting('general.ns2') ?? null,
                setting('general.ns3') ?? null,
                setting('general.ns4') ?? null,
            ],
            'admin_ns' => "root.{$adminNs}",
            'serial' => $serial,
            'refresh' => $refresh,
            'retry' => $retry,
            'expire' => $expire,
            'negativeCache' => $negativeCache,
            'nsIp' => $server,
            'records' => $zones
        ];
    }

    public function updateZoneForwardConfig(array $recordsData, string $domain)
    {
        $confPath = "/var/named/{$domain}.db";

        $zoneForwardConfig = view('server.samples.bind9.bind9_named_zones_forward', [
            'bind9ForwardData' => $recordsData
        ])->render();

        file_put_contents($confPath, $zoneForwardConfig);
    }

    public function updateZoneReverseConfig(array $recordsData, string $ip, string $domain)
    {
        $revIp = explode('.', $ip);
        $revIp = array_reverse($revIp);
        $revIp = implode('.', $revIp);

        $confPath = "/var/named/{$revIp}.rev";

//        $ip = explode('.', $recordsData['nsIp']);
//        $lastOct = end($ip);
//        $recordsData['lastOct'] = $lastOct;

        $recordsData['revIp'] = $revIp;

        $zoneReverseConfig = view('server.samples.bind9.bind9_named_zones_reverse', [
            'bind9ReverseData' => $recordsData
        ])->render();

        file_put_contents($confPath, $zoneReverseConfig, $domain);

//        if($this->checkService() === 'pdns') {
//            $commands = [
//                "pdns_control bind-add-zone {$domain} {$confPath}",
//                'pdns_control reload'
//            ];
//            foreach ($commands as $command) {
//                shell_exec($command);
//            }
//        }
    }

    public function updateResolv()
    {
        $filePath = '/etc/resolv.conf';
        $server = $this->getNsIp();
        $nameservers[] = $server;

        $command = "nmcli dev show | grep -E 'IP4.DNS|IP6.DNS' | awk '{print $2}'";
        $output = shell_exec($command);
        $addresses = explode("\n", trim($output));
        $nameservers = array_merge($nameservers, $addresses);

        $resolved = view('server.samples.bind9.bind9_resolv_conf', [
            'bind9Nameservers' => $nameservers
        ])->render();

        file_put_contents($filePath, $resolved);
    }

    public function getCurrentDomains()
    {

        $domainData = ZoneEditor::pluck('domain')->unique();
        $currentDomains = [];
        foreach ($domainData as $domain) {
            $currentDomains[] = $domain;
        }

        return array_unique($currentDomains);
    }

//    public function getDomainTrustedIps()
//    {
//        $domainData = ZoneEditor::where('hosting_subscription_id', $this->hostingSubscription->id)
//            ->get();
//
//        $server = $this->getNsIp();
//
//        $trustedIps = [];
//        $trustedIps[] = '127.0.0.1';
//        $trustedIps[] = $server;
//
//        foreach ($domainData as $trustedIp) {
//            if (filter_var($trustedIp->record, FILTER_VALIDATE_IP)) {
//                $trustedIps[] = $trustedIp->record;
//            }
//        }
//
//        return empty($trustedIps) ? [] : array_unique($trustedIps);
//    }

    public function getNsIp()
    {
        $command = 'hostname -I | awk \'{print $1}\'';
        $output = shell_exec($command);
        $server = trim($output);
        return $server;
    }

    public function getRevIps(array $ips)
    {

        $revIps = [];

        foreach ($ips as $ip) {
            $revIp = explode('.', $ip);
            $revIp = array_reverse($revIp);
            $revIps[] = implode('.', $revIp);
        }

        return $revIps;
    }

    public function checkService(): string
    {
//        return  (setting('server_config.nameserver_select_pdns') !== false) ? 'pdns' :
//            ((setting('server_config.nameserver_select_bind') !== false) ? 'named' : 'none');

        return 'named';
    }

//    public function addZonesToPdns(): void
//    {
//        $commands = [];
//        $domainData = $this->getCurrentDomains();
//
//        foreach ($domainData as $domain) {
//            $commands[] = "pdns_control bind-add-zone {$domain} /var/named/{$domain}.db";
//        }
//        $commands[] = 'pdns_control reload';
//
//        foreach ($commands as $command) {
//            shell_exec($command);
//        }
//    }
}
