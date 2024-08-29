<?php

namespace App\Server\Helpers;

class Fail2Ban
{
    public static function getFail2BanAvailableJails()
    {

        $activeServices = [];
        $command = "fail2ban-client status | grep 'Jail list'";
        $fail2BanJails = shell_exec($command);

        if ($fail2BanJails !== null) {
            $jails = trim($fail2BanJails);

            if (preg_match('/Jail list:\s*(.*)/', $jails, $matches)) {
                $jailList = $matches[1];
                $activeJails = explode(', ', $jailList);

                foreach ($activeJails as $service) {
                    $activeServices[$service] = $service;
                }
            }
        }

        return $activeServices;
    }

    public static function getFail2BanTimeUnits()
    {

        $timeUnits = [];
        $f2bTime = [
            's' => 'second/s',
            'm' => 'minute/s',
            'h' => 'hour/s'
        ];

        foreach ($f2bTime as $unit => $timeUnit) {
            $timeUnits[$unit] = $timeUnit;
        }

        return $timeUnits;

    }

    public static function getFial2BanGeneralBackend()
    {

        $backend = [];
        $f2BBackend = [
            'auto' => 'auto',
            'pyinotify' => 'pyinotify',
            'gamin' => 'gamin',
            'polling' => 'polling',
            'systemd' => 'systemd'
        ];

        foreach ($f2BBackend as $name => $bEnd) {
            $backend[$name] = $bEnd;
        }

        return $backend;
    }

    public static function getFail2BanGeneralUsedns()
    {
        $usedns = [];
        $f2BUsedns = [
            'warn' => 'warn',
            'yes' => 'yes',
            'but' => 'but',
            'no' => 'no',
            'raw' => 'raw'
        ];

        foreach ($f2BUsedns as $name => $bUsedns) {
            $usedns[$name] = $bUsedns;
        }

        return $usedns;
    }

    public static function getFail2BanGeneralLogencoding()
    {

        $logencoding = [];
        $f2bLogencoding = [
            'auto' => 'auto',
            'ascii' => 'ascii',
            'utf8' => 'utf-8'
        ];

        foreach ($f2bLogencoding as $name => $lEncoding) {
            $logencoding[$name] = $lEncoding;
        }

        return $logencoding;
    }

    public static function getFail2BanActionsMta()
    {
        $actions = [];
        $f2bActions = [
            'sendmail' => 'sendmail',
            'mail' => 'mail'
        ];

        foreach ($f2bActions as $name => $action) {
            $actions[$name] = $action;
        }

        return $actions;
    }

    public static function getFail2BanActionsProtocol()
    {
        $protocols = [];
        $f2bProtocols = [
            'tcp' => 'tcp',
            'udp' => 'udp',
        ];

        foreach ($f2bProtocols as $name => $protocol) {
            $protocols[$name] = $protocol;
        }

        return $protocols;
    }

    public static function getFail2BanBanactions()
    {
        $banactions = [];
        $f2bBanactions = [
            'iptables' => 'iptables',
            'iptables-new' => 'iptables-new',
            'iptables-multipot' => 'iptables-multiport',
            'shorewall' => 'shorewall',
            'firewalld-allports' => 'firewalld-allports',
            'firewallcmd-rich-rules' => 'firewalld-rich-rules',
            'firewalld-ipset' => 'firewalld-ipset',
            'firewalld-multiport' => 'firewalld-multiport',
        ];

        foreach ($f2bBanactions as $name => $banaction) {
            $banactions[$name] = $banaction;
        }

        return $banactions;
    }

    public static function getFail2BanJailFilters($jail)
    {

        $filters = [];
        $filterPath = '/etc/fail2ban/filter.d';
        $pattern = $filterPath . "/{$jail}*.conf";

        $files = glob($pattern);
        if ($files) {
            foreach ($files as $file) {
                $file = pathinfo(basename($file), PATHINFO_FILENAME);
                $filters[$file] = $file;
            }
        }
        return $filters;
    }

    public function getFail2BanProtocols()
    {

        $protocols = [];
        $fail2BanProtocols = [
            'tcp' => 'tcp'
        ];

        foreach ($fail2BanProtocols as $name => $protocol) {
            $protocols[$name] = $protocol;
        }

        return $protocols;
    }
}
