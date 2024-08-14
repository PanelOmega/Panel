<?php

namespace App\Server;

class SupportedApplicationTypes
{
    public static function getNodeJsVersions()
    {
        $versions = [];
        $nodeJsVersions = [
            '14',
            '16',
            '17',
            '18',
            '19',
            '20',
        ];
        foreach ($nodeJsVersions as $version) {
            $versions[$version] = 'Node.js ' . $version;
        }
        return $versions;
    }

    public static function getRubyVersions()
    {
        $versions = [];
        $rubyVersions = [
            '2.7',
            '3.0',
            '3.1',
            '3.2',
            '3.3',
            '3.4',
        ];
        foreach ($rubyVersions as $version) {
            $versions[$version] = 'Ruby ' . $version;
        }
        return $versions;
    }

    public static function getPythonVersions()
    {
        $versions = [];
        $pythonVersions = [
            '2.7',
            '3.6',
            '3.7',
            '3.8',
            '3.9',
            '3.10',
        ];
        foreach ($pythonVersions as $version) {
            $versions[$version] = 'Python ' . $version;
        }
        return $versions;
    }

    public static function getPHPVersions()
    {
        $versions = [];
        $phpVersions = [
            '5.6',
            '7.4',
            '8.0',
            '8.1',
            '8.2',
            '8.3',
        ];
        foreach ($phpVersions as $version) {
            $versions[$version] = 'PHP ' . $version;
        }
        return $versions;
    }

    public static function getPHPModules()
    {
        $modules = [];
        $phpModules = [
            'bcmath' => 'BCMath',
            'bz2' => 'Bzip2',
            'calendar' => 'Calendar',
            'ctype' => 'Ctype',
            'curl' => 'Curl',
            'dom' => 'DOM',
            'fileinfo' => 'Fileinfo',
            'gd' => 'GD',
            'intl' => 'Intl',
            'mbstring' => 'Mbstring',
            'mysql' => 'MySQL',
            'opcache' => 'OPcache',
            'sqlite3' => 'SQLite3',
            'xmlrpc' => 'XML-RPC',
            'zip' => 'Zip',
        ];
        foreach ($phpModules as $module => $name) {
            $modules[$module] = $name;
        }
        return $modules;
    }

    public static function getFail2BanAvailableJails()
    {

        $activeServices = [];
        $command = "fail2ban-client status | grep 'Jail list'";
        $fail2BanJails = shell_exec($command);

        if($fail2BanJails !== null) {
            $jails = trim($fail2BanJails);

            if (preg_match('/Jail list:\s*(.*)/', $jails, $matches)) {
                $jailList = $matches[1];
                $activeJails = explode(', ', $jailList);

                foreach($activeJails as $service) {
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

        foreach($f2bTime as $unit => $timeUnit) {
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

    public static function getFail2BanJailFilters($jail) {

        $filters = [];
        $filterPath = '/etc/fail2ban/filter.d';
        $pattern = $filterPath . "/{$jail}*.conf";

        $files = glob($pattern);
        if($files) {
            foreach($files as $file) {
                $file = pathinfo(basename($file), PATHINFO_FILENAME);
                $filters[$file] = $file;
            }
        }
        return $filters;
    }

    public static function getIndexesIndexTypes() {
        $types = [];
        $indexTypes = [
            'inherit' => 'Inherit',
            'no_indexing' => 'No Indexing',
            'show_filename_only' => 'Show Filename Only',
            'show_filename_and_description' => 'Show Filename And Description',
        ];

        foreach($indexTypes as $name => $type) {
            $types[$name] = $type;
        }

        return $types;
    }

    public function getFail2BanProtocols() {

        $protocols = [];
        $fail2BanProtocols = [
            'tcp' => 'tcp'
        ];

        foreach($fail2BanProtocols as $name => $protocol) {
            $protocols[$name] = $protocol;
        }

        return $protocols;
    }
}
