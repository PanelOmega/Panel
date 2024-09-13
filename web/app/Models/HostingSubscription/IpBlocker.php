<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildIpBlocker;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;

class IpBlocker extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'blocked_ip',
        'beginning_ip',
        'ending_ip',
    ];

    protected $table = 'hosting_subscription_ip_blockers';

    public static function boot()
    {
        parent::boot();
        static::ipBlockerBoot();
    }

    public static function ipBlockerBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $ipBlockerPath = "/home/{$hostingSubscription->system_username}/public_html/.htaccess";

        $callback = function () use ($hostingSubscription, $ipBlockerPath) {
            $htaccessBuild = new HtaccessBuildIpBlocker(false, $ipBlockerPath, $hostingSubscription->id);
            $htaccessBuild->handle();
        };

        static::created($callback);
        static::deleted($callback);
    }

    public static function prepareIpBlockerRecords($record, $hostingSubscriptionId)
    {

        $ipBlockerRecords = [];
        if (strpos($record['blocked_ip'], '-') !== false) {
            [$startIp, $endIpPart] = explode('-', $record['blocked_ip']);

            if (!filter_var($endIpPart, FILTER_VALIDATE_IP)) {
                $endIpParts = explode('.', $startIp);
                $endIpParts[3] = $endIpPart;
                $endIp = implode('.', $endIpParts);
            } else {
                $endIp = $endIpPart;
            }

            if (filter_var($startIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($endIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $cidrRanges = self::generateCidrBlocks($startIp, $endIp);
                foreach ($cidrRanges as $cidr) {
                    [$blockStartIp, $blockEndIp] = self::cidrToRange($cidr);
                    $ipBlockerRecords[] = [
                        'hosting_subscription_id' => $hostingSubscriptionId,
                        'blocked_ip' => $cidr,
                        'beginning_ip' => $blockStartIp,
                        'ending_ip' => $blockEndIp,
                    ];
                }
            }

        } elseif (strpos($record['blocked_ip'], '/')) {
            [$startIp, $endIp] = self::getLastIpInCidrRange($record['blocked_ip']);
            $cidrRanges = self::generateCidrBlocks($startIp, $endIp);
            foreach ($cidrRanges as $cidr) {
                [$blockStartIp, $blockEndIp] = self::cidrToRange($cidr);
                $ipBlockerRecords[] = [
                    'hosting_subscription_id' => $hostingSubscriptionId,
                    'blocked_ip' => $cidr,
                    'beginning_ip' => $blockStartIp,
                    'ending_ip' => $blockEndIp,
                ];
            }
        } else {
            $ipServerSetting = '';
            $beginningIp = '';
            $endingIp = '';
            if (preg_match('/^\d{1,3}\.$/', $record['blocked_ip'])) {
                $ipServerSetting = self::fillIncompleteIp($record['blocked_ip']);
                $beginningIp = "{$record['blocked_ip']}0.0.0";
                $endingIp = "{$record['blocked_ip']}255.255.255";
            } elseif (filter_var($record['blocked_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipServerSetting = $record['blocked_ip'];
                $beginningIp = $ipServerSetting;
                $endingIp = $ipServerSetting;
            } else {
                throw new Exception("Invalid IP address!");
            }


            if (!empty($beginningIp) && !empty($endingIp)) {
                $ipBlockerRecords[] = [
                    'hosting_subscription_id' => $hostingSubscriptionId,
                    'blocked_ip' => $ipServerSetting,
                    'beginning_ip' => $beginningIp,
                    'ending_ip' => $endingIp,
                ];
            }
        }

        return self::filterIpBlockersArr($ipBlockerRecords);
    }

    public static function generateCidrBlocks($startIp, $endIp)
    {
        $cidrBlocks = [];

        $isIpv4 = filter_var($startIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;

        if ($isIpv4) {
            $start = ip2long($startIp);
            $end = ip2long($endIp);

            if ($start > $end) {
                array_push($cidrBlocks, long2ip($start));
            } else {

                while ($end >= $start) {
                    $maxSize = 32;
                    while ($maxSize > 0) {
                        $mask = hexdec(base_convert((pow(2, 32) - pow(2, (32 - ($maxSize - 1)))), 10, 16));
                        $maskBase = $start & $mask;

                        if ($maskBase != $start) {
                            break;
                        }
                        $maxSize--;
                    }
                    $x = log($end - $start + 1) / log(2);
                    $maxDiff = floor(32 - floor($x));

                    if ($maxSize < $maxDiff) {
                        $maxSize = $maxDiff;
                    }

                    $ip = long2ip($start);
                    $blockEnd = $start + pow(2, (32 - $maxSize)) - 1;

                    if ($blockEnd >= $end) {
                        if ($blockEnd == $end && $start == $end) {
                            array_push($cidrBlocks, $ip);
                        } else {
                            array_push($cidrBlocks, "$ip/$maxSize");
                        }
                        break;
                    } else {
                        if ($maxSize === 32) {
                            array_push($cidrBlocks, $ip);
                        } else {
                            array_push($cidrBlocks, "{$ip}/{$maxSize}");
                        }
                        $start += pow(2, (32 - $maxSize));
                    }
                }
            }
        } else {
            throw new Exception('Ipv6 currently unavailable!');
        }

        return $cidrBlocks;
    }

    public static function cidrToRange($cidr)
    {

        $parts = explode('/', $cidr);
        $ip = $parts[0];
        $prefixLength = isset($parts[1]) ? (int)$parts[1] : 32;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $mask = 0xFFFFFFFF << (32 - $prefixLength);
            $ipLong = ip2long($ip);
            $start = $ipLong & $mask;
            $end = $start | (~$mask & 0xFFFFFFFF);
            return [long2ip($start), long2ip($end)];
        } else {
            throw new Exception('Ipv6 currently unavailable!');
        }
    }

    public static function getLastIpInCidrRange($cidr)
    {
        list($ip, $prefix) = explode('/', $cidr);
        $prefix = (int)$prefix;
        $ipLong = ip2long($ip);
        $mask = -1 << (32 - $prefix);
        $network = $ipLong & $mask;
        $lastIp = $network | ~$mask;
        return [$ip, long2ip($lastIp)];
    }

    public static function fillIncompleteIp($ip)
    {
        $ip = trim($ip, '.');
        $parts = explode('.', $ip);
        while (count($parts) < 4) {
            $parts[] = '0';
        }
        $fullIp = implode('.', $parts);

        return $fullIp . '/8';
    }

    public static function filterIpBlockersArr($ipBlockerRecords)
    {
        $filteredRecords = [];
        $previouslyBlockedIps = IpBlocker::all()->toArray();
        $addresses = array_column($previouslyBlockedIps, 'blocked_ip');

        foreach ($ipBlockerRecords as $record) {
            if (!in_array($record['blocked_ip'], $addresses)) {
                $filteredRecords[] = $record;
            }
        }
        return $filteredRecords;
    }
}
