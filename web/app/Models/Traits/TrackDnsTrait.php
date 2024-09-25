<?php

namespace App\Models\Traits;

trait TrackDnsTrait
{
    public static function getDomainAddresses(string $host)
    {
        $result = [];
        if(!empty($host)) {
            $records = dns_get_record($host, DNS_A | DNS_AAAA | DNS_MX);

            if (count($records) > 0) {
                foreach ($records as $recordVal) {
                    switch ($recordVal['type']) {
                        case 'A':
                            $result[] = "{$recordVal['host']} has address {$recordVal['ip']}";
                            break;
                        case 'AAAA':
                            $result[] = "{$recordVal['host']} has address {$recordVal['ipv6']}";
                            break;
                        case 'MX':
                            $result[] = "{$recordVal['host']} mail is handled by {$recordVal['pri']} {$recordVal['target']}.";
                            break;
                    }
                }
            }
        }
        return $result;
    }

    public static function getDomainZoneInformation(string $host)
    {
        $records = dns_get_record($host, DNS_ALL);
        $result = [];

        if (count($records) > 0) {
            foreach ($records as $dataVal) {
                switch ($dataVal['type']) {
                    case 'A':
                        $result[] = "{$dataVal['host']}.\t{$dataVal['ttl']} {$dataVal['class']}\t{$dataVal['type']}\t{$dataVal['ip']}";
                        break;
                    case 'AAAA':
                        $result[] = "{$dataVal['host']}.\t{$dataVal['ttl']} {$dataVal['class']}\t{$dataVal['type']}\t{$dataVal['ipv6']}";
                        break;
                    case 'NS':
                        $result[] = "{$dataVal['host']}.\t{$dataVal['ttl']} {$dataVal['class']}\t{$dataVal['type']}\t{$dataVal['target']}";
                        break;
                    case 'SOA':
                        $result[] = "{$dataVal['host']}.\t{$dataVal['ttl']} {$dataVal['class']}\t{$dataVal['type']}\t{$dataVal['mname']}\t{$dataVal['rname']}\t{$dataVal['serial']}\t{$dataVal['refresh']}\t{$dataVal['retry']}\t{$dataVal['expire']}\t{$dataVal['minimum-ttl']}";
                        break;
                    case 'MX':
                        $result[] = "{$dataVal['host']}.\t{$dataVal['ttl']} {$dataVal['class']}\t{$dataVal['type']}\t{$dataVal['pri']}\t{$dataVal['target']}";
                        break;
                    case 'TXT':
                        $result[] = "{$dataVal['host']}.\t{$dataVal['ttl']} {$dataVal['class']}\t{$dataVal['type']}\t{$dataVal['txt']}";
                        break;
                    case 'CAA':
                        $result[] = "{$dataVal['host']}.\t{$dataVal['ttl']} {$dataVal['class']}\t{$dataVal['type']}\t{$dataVal['flags']}\t{$dataVal['tag']}\t{$dataVal['value']}";
                        break;
                }
            }
        }
        return $result;
    }

    public static function getTraceroute(string $host) {

        $host = $host ?: explode(' ', shell_exec('hostname -I'))[0];

        if(empty($host)) {
           return [];
        }

        $command = "traceroute {$host}";
        $output = shell_exec($command);

        if (empty($output)) {
            return ['error' => 'No output from traceroute'];
        }

        $lines = preg_split('/\R/', trim($output));
        $result = ['host' => array_shift($lines)];

        for($i = 0; $i < 30; $i++) {
            $result[] = $lines[$i] ?? " " . ($i + 1) .  "  * * *";
        }
        return $result;
    }
}
