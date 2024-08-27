<?php

namespace App\Models;

use App\Jobs\HtaccessBuildIpBlocker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpBlocker extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'blocked_ip',
        'beginning_ip',
        'ending_ip',
    ];

    public static function boot() {
        parent::boot();
        static::ipBlockerBoot();
    }

    public static function ipBlockerBoot() {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $ipBlockerPath = "/home/{$hostingSubscription->system_username}/public_html/.htaccess";

        $callback = function() use ($hostingSubscription, $ipBlockerPath) {
            $htaccessBuild = new HtaccessBuildIpBlocker(false, $ipBlockerPath, $hostingSubscription->id);
            $htaccessBuild->handle();
        };

        static::created($callback);
        static::deleted($callback);
    }

    public static function prepareIpBlockerRecords($record, $hostingSubscriptionId) {

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
            } elseif(filter_var($startIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && filter_var($endIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $startIp = self::formatIPv6($startIp);
                $endIp = self::formatIPv6($endIp);

                $cidrRanges = self::generateCidrBlocks($startIp, $endIp);
                foreach($cidrRanges as $cidr) {
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
                $ipServerSetting = $record['blocked_ip'];
                $ipServerSetting = self::fillIncompleteIp($record['blocked_ip']);
                $beginningIp = "{$record['blocked_ip']}0.0.0";
                $endingIp = "{$record['blocked_ip']}255.255.255";
            } elseif (filter_var($record['blocked_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipServerSetting = $record['blocked_ip'];
                $beginningIp = $ipServerSetting;
                $endingIp = $ipServerSetting;
            } elseif (filter_var($record['blocked_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $ipServerSetting = self::formatIPv6($record['blocked_ip']);
                $beginningIp = $ipServerSetting;
                $endingIp = $ipServerSetting;
            }

            if(!empty($beginningIp) && !empty($endingIp)) {
                $ipBlockerRecords[] = [
                    'hosting_subscription_id' => $hostingSubscriptionId,
                    'blocked_ip' => $ipServerSetting,
                    'beginning_ip' => $beginningIp,
                    'ending_ip' => $endingIp,
                ];
            }
        }

        return $ipBlockerRecords;
    }

    public static function generateCidrBlocks($startIp, $endIp) {
        $cidrBlocks = [];

        $isIpv4 = filter_var($startIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;

        if ($isIpv4) {
            $start = ip2long($startIp);
            $end = ip2long($endIp);

            if($start > $end) {
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
                        if($maxSize === 32) {
                            array_push($cidrBlocks, $ip);
                        } else {
                            array_push($cidrBlocks, "${ip}/{$maxSize}");
                        }
                        $start += pow(2, (32 - $maxSize));
                    }
                }
            }
        } else {

            $start = inet_pton($startIp);
            $end = inet_pton($endIp);

            $startGmp = gmp_init(bin2hex($start), 16);
            $endGmp = gmp_init(bin2hex($end), 16);

            $cidrBlocks[] = self::formatIPv6(inet_ntop($start));
            $startGmp = gmp_add($startGmp, 1);

            while (strcmp($end, $start) >= 0) {
                $maxSize = 128;

                while ($maxSize > 0) {
                    $mask = str_repeat('f', floor($maxSize / 4));
                    if ($maxSize % 4 != 0) {
                        $mask .= dechex((1 << (4 - $maxSize % 4)) - 1);
                    }
                    $mask = str_pad($mask, 32, '0', STR_PAD_RIGHT);
                    $maskBin = hex2bin($mask);
                    $networkBase = $start & $maskBin;

                    if ($networkBase != $start) {
                        break;
                    }
                    $maxSize--;
                }

                $cidr = bin2hex($start);
                $cidr = strtoupper(chunk_split($cidr, 4, ':'));
                $cidr = rtrim($cidr, ':');
                $cidrBlocks[] = $cidr . "/$maxSize";
                $increment = pow(2, (128 - $maxSize));
                $gmpValue = gmp_init(bin2hex($start), 16);
                $newGmpValue = gmp_add($gmpValue, $increment);
                $newHex = gmp_strval($newGmpValue, 16);
                $start = str_pad($newHex, 32, '0', STR_PAD_LEFT);
            }
        }
        return $cidrBlocks;
    }

    public static function formatIPv6($ip) {
        $packedIp = inet_pton($ip);
        $expandedIp = str_pad(bin2hex($packedIp), 32, '0', STR_PAD_LEFT);
        $blocks = str_split($expandedIp, 4);
        foreach ($blocks as &$block) {
            $block = str_pad($block, 4, '0', STR_PAD_LEFT);
        }
        $fullIp = implode(':', $blocks);
        return $fullIp;
    }

    public static function cidrToRange($cidr) {

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
            $ipBinary = inet_pton($ip);
            $addr_given_hex = bin2hex($ipBinary);

            $addr_given_str = inet_ntop($ipBinary);

            $flexbits = 128 - $prefixLength;

            $addr_hex_first = $addr_given_hex;
            $addr_hex_last = $addr_given_hex;

            $pos = 31;
            while ($flexbits > 0) {
                $orig_first = substr($addr_hex_first, $pos, 1);
                $orig_last = substr($addr_hex_last, $pos, 1);

                $origval_first = hexdec($orig_first);
                $origval_last = hexdec($orig_last);

                $mask = 0xf << (min(4, $flexbits));

                $new_val_first = $origval_first & $mask;

                $new_val_last = $origval_last | (pow(2, min(4, $flexbits)) - 1);

                $new_first = dechex($new_val_first);
                $new_last = dechex($new_val_last);

                $addr_hex_first = substr_replace($addr_hex_first, $new_first, $pos, 1);
                $addr_hex_last = substr_replace($addr_hex_last, $new_last, $pos, 1);

                $flexbits -= 4;
                $pos -= 1;
            }

            $addr_bin_first = hex2bin($addr_hex_first);
            $addr_bin_last = hex2bin($addr_hex_last);

            $addr_str_first = inet_ntop($addr_bin_first);
            $addr_str_last = inet_ntop($addr_bin_last);

            return [self::formatIPv6($addr_str_first), self::formatIPv6($addr_str_last)];
        }
    }

    public static function getLastIpInCidrRange($cidr) {
        list($ip, $prefix) = explode('/', $cidr);
        $prefix = (int)$prefix;
        $ipLong = ip2long($ip);
        $mask = -1 << (32 - $prefix);
        $network = $ipLong & $mask;
        $lastIp = $network | ~$mask;
        return [$ip, long2ip($lastIp)];
    }

    public static function fillIncompleteIp($ip) {
        $ip = trim($ip, '.');
        $parts = explode('.', $ip);
        while (count($parts) < 4) {
            $parts[] = '0';
        }
        $fullIp = implode('.', $parts);

        return $fullIp . '/8';
    }

    public static function createIpv6Mask($prefixLength) {
        $mask = str_repeat('f', $prefixLength / 4);
        if ($prefixLength % 4 != 0) {
            $mask .= dechex(15 << (4 - $prefixLength % 4));
        }
        $mask = str_pad($mask, 32, '0');
        return pack('H*', $mask);
    }
}
