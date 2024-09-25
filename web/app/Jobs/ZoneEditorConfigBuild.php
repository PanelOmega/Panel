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

    public function __construct($fixPermissions = false, $hostingSubscription, $domain) {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscription = $hostingSubscription;
        $this->domain = $domain;
    }

    public function handle() {
        $this->udpateConfigs();
        $this->updateZoneFile();
        $recordsData = $this->getZonesData();
        $this->updateZoneForwardConfig($recordsData);
        $service = $this->checkService();
        shell_exec("systemctl restart {$service}");
    }

    public function udpateConfigs() {
        $confPath = '/etc/named.conf';
        $ips = ZoneEditor::where('domain', $this->domain)
            ->pluck('record');

        $server = $this->getNs1();
        $trustedIps = [];
        $trustedIps[] = '127.0.0.1';
        $trustedIps[] = $server;

        foreach($ips as $trustedIp) {
            if(filter_var($trustedIp, FILTER_VALIDATE_IP)) {
                $trustedIps[] = $trustedIp;
            }
        }

        $portV4Ips = implode('; ', $trustedIps);

        $bind9ConfigData = [
            'aclTrusted' => $trustedIps,
            'portV4Ips' => $portV4Ips . ';',
            'forwarders' => [
                '8.8.8.8',
                '8.8.4.4'
            ],
            'dnsValidation' => 'auto',
            'domains' => [$this->domain]
        ];

        $bind9Config = view('server.samples.bind9.bind9_named_conf', [
            'bind9Data' => $bind9ConfigData
        ])->render();

        file_put_contents($confPath, $bind9Config);
        $this->updateResolv($server);
    }

    public function updateZoneFile() {
        $confPath = "/etc/named.{$this->domain}.zones";
        $bind9ZonesData = [
            [
                'domain' => $this->domain,
            ],
        ];

        $bind9Zones = view('server.samples.bind9.bind9_named_zones', [
            'bind9Zones' => $bind9ZonesData
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
        $zones = ZoneEditor::where('hosting_subscription_id', $this->hostingSubscription->id)->get()->toArray();

        $server = $this->getNs1();

        return [
            'ttl' => $ttl,
            'domain' => $this->domain,
            'serial' => $serial,
            'refresh' => $refresh,
            'retry' => $retry,
            'expire' => $expire,
            'negativeCache' => $negativeCache,
            'ns1' => $server,
            'records' => $zones
        ];
    }

    public function updateZoneForwardConfig(array $recordsData) {
        $confPath = "/etc/named.{$this->domain}.db";

        $zoneForwardConfig = view('server.samples.bind9.bind9_named_zones_forward', [
            'bind9ForwardData' => $recordsData
        ])->render();

        file_put_contents($confPath, $zoneForwardConfig);

        if($this->checkService() === 'pdns') {
            $commands = [
                "pdns_control bind-add-zone {$this->domain} {$confPath}",
                "pdns_control bind-reload-now {$this->domain}",
            ];

            foreach($commands as $command) {
                shell_exec($command);
            }
        }
    }

    public function updateResolv() {

        $filePath = '/etc/resolv.conf';
        $server = $this->getNs1();
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

    public function getNs1() {
        $command = 'hostname -I | awk \'{print $1}\'';
        $output = shell_exec($command);
        $server = trim($output);
        return $server;
    }

    public function checkService(): string {
        return (strpos(shell_exec('systemctl is-active named'), 'active') !== false) ? 'named' :
            ((strpos(shell_exec('systemctl is-active pdns'), 'active') !== false) ? 'pdns' : 'none');
    }
}
