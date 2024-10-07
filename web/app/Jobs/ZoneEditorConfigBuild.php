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

    public $domain;

    public $ip;

    public function __construct($fixPermissions = false, $hostingSubscription, $domain, $ip = null) {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscription = $hostingSubscription;
        $this->domain = $domain;
        $this->ip = $ip;
    }

    public function handle() {
        $this->udpateConfigs();
        $this->updateZoneFile();
        $recordsData = $this->getZonesData();
        $this->updateZoneForwardConfig($recordsData);
        if($this->ip !== null) {
            $this->updateZoneReverseConfig($recordsData, $this->ip);
        }
        $service = $this->checkService();
        shell_exec("systemctl restart {$service}");
    }

    public function udpateConfigs() {
        $confPath = '/etc/named.conf';
        $dnsZonesPath = '/etc/named.panelomega.zones';

        $server = $this->getNsIp();
        $trustedIps = $this->getDomainTrustedIps();

        $bind9ConfigData = [
            'aclTrusted' => $trustedIps,
//            'portV4Ips' => 'trusted;',
            'forwarders' => [
                '8.8.8.8',
                '8.8.4.4'
            ],
            'dnsValidation' => 'auto',
            'dnsZones' => $dnsZonesPath
        ];

        $bind9Config = view('server.samples.bind9.bind9_named_conf', [
            'bind9Data' => $bind9ConfigData
        ])->render();

        file_put_contents($confPath, $bind9Config);
        $this->updateResolv($server);
    }

    public function updateZoneFile() {

        $domains = $this->getCurrentDomains();

        $revIpData = [
            $this->getNsIp(),
            $this->ip
        ];
        $revIps = $this->getRevIps($revIpData);

        $confPath = "/etc/named.panelomega.zones";

        $forwardZonesData = [];
        foreach($domains as $domain) {
            $forwardZonesData[] = [
                'domain' => $domain
            ];
        }

        $reverseZonesData = [];
        foreach($revIps as $ip) {
            $reverseZonesData[] = [
                'ip' => $ip
            ];
        }

        $bind9Zones = view('server.samples.bind9.bind9_named_zones', [
            'forwardZones' => $forwardZonesData,
//            'reverseZones' => $reverseZonesData
        ])->render();
        file_put_contents($confPath, $bind9Zones);
    }

    public function getZonesData() {
        $ttl = 14400;
        $serial = now()->format('Ymd') . '01';
        $refresh = 30800;
        $retry = 1800;
        $expire = 1209600;
        $negativeCache = 86400;
        $zones = ZoneEditor::where('hosting_subscription_id', $this->hostingSubscription->id)
            ->where('domain', $this->domain)
            ->get()
            ->toArray();

        $server = $this->getNsIp();

        return [
            'ttl' => $ttl,
            'domain' => $this->domain,
            'serial' => $serial,
            'refresh' => $refresh,
            'retry' => $retry,
            'expire' => $expire,
            'negativeCache' => $negativeCache,
            'nsIp' => $server,
            'records' => $zones
        ];
    }

    public function updateZoneForwardConfig(array $recordsData) {
        $confPath = "/etc/named.{$this->domain}.db";

        $zoneForwardConfig = view('server.samples.bind9.bind9_named_zones_forward', [
            'bind9ForwardData' => $recordsData
        ])->render();

        file_put_contents($confPath, $zoneForwardConfig);

//        if($this->checkService() === 'pdns') {
            $commands = [
                "pdns_control bind-add-zone {$this->domain} {$confPath}",
                "pdns_control reload"
            ];
            foreach($commands as $command) {
                shell_exec($command);
            }
//        }
    }

    public function updateZoneReverseConfig(array $recordsData, string $ip) {

        $revIp = explode('.', $ip);
        $revIp = array_reverse($revIp);
        $revIp = implode('.', $revIp);

        $confPath = "/etc/named.{$revIp}.rev";

//        $ip = explode('.', $recordsData['nsIp']);
//        $lastOct = end($ip);
//        $recordsData['lastOct'] = $lastOct;

        $recordsData['revIp'] = $revIp;

        $zoneReverseConfig = view('server.samples.bind9.bind9_named_zones_reverse', [
            'bind9ReverseData' => $recordsData
        ])->render();

        file_put_contents($confPath, $zoneReverseConfig);

//        if($this->checkService() === 'pdns') {
        $commands = [
            "pdns_control bind-add-zone {$this->domain} {$confPath}",
            'pdns_control reload'
        ];
        foreach ($commands as $command) {
            shell_exec($command);
        }
//        }
    }

    public function updateResolv() {

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

    public function getCurrentDomains() {
        $domainData = ZoneEditor::where('hosting_subscription_id', $this->hostingSubscription->id)
            ->get();

        $currentDomains = [];
        foreach($domainData as $domain) {
            $currentDomains[] = $domain->domain;
        }

        return array_unique($currentDomains);
    }

    public function getDomainTrustedIps() {
        $domainData = ZoneEditor::where('hosting_subscription_id', $this->hostingSubscription->id)
            ->get();

        $server = $this->getNsIp();

        $trustedIps = [];
        $trustedIps[] = '127.0.0.1';
        $trustedIps[] = $server;

        foreach($domainData as $trustedIp) {
            if(filter_var($trustedIp->record, FILTER_VALIDATE_IP)) {
                $trustedIps[] = $trustedIp->record;
            }
        }

        return empty($trustedIps) ? [] : array_unique($trustedIps);
    }

    public function getNsIp() {
        $command = 'hostname -I | awk \'{print $1}\'';
        $output = shell_exec($command);
        $server = trim($output);
        return $server;
    }

    public function getRevIps(array $ips) {

        $revIps = [];

        foreach($ips as $ip) {
            $revIp = explode('.', $ip);
            $revIp = array_reverse($revIp);
            $revIps[] = implode('.', $revIp);
        }

        return $revIps;
    }

    public function checkService(): string {
        return (strpos(shell_exec('systemctl is-active named'), 'active') !== false) ? 'named' :
            ((strpos(shell_exec('systemctl is-active pdns'), 'active') !== false) ? 'pdns' : 'none');
    }
}
