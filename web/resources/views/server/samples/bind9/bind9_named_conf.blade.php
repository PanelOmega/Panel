//
// named.conf
//
// Provided by Red Hat bind package to configure the ISC BIND named(8) DNS
// server as a caching only nameserver (as a localhost DNS resolver only).
//
// See /usr/share/doc/bind*/sample/ for example named configuration files.
//

acl trusted {
@if(isset($bind9Data['aclTrusted']))
    @foreach($bind9Data['aclTrusted'] as $aclTrusted)
        {{ $aclTrusted }};
    @endforeach
@endif
};

options {
listen-on port 53 @if(isset($bind9Data['portV4Ips'])) {{ '{ ' . $bind9Data['portV4Ips'] . ' }' }} @else { } @endif;
listen-on-v6 port 53 @if(isset($bind9Data['portV6Ips'])) {{ '{ ' . $bind9Data['portV6Ips'] . ' }' }} @else { } @endif;
directory       "/var/named";
dump-file       "/var/named/data/cache_dump.db";
statistics-file "/var/named/data/named_stats.txt";
memstatistics-file "/var/named/data/named_mem_stats.txt";
secroots-file   "/var/named/data/named.secroots";
recursing-file  "/var/named/data/named.recursing";
allow-query @if(isset($bind9Data['allowQuery'])) {{ '{' . $bin9Data['allowQuery'] . '}' }} @else { localhost; } @endif;

@if(isset($bind9Data['forwarders']))
forwarders {
    @foreach($bind9Data['forwarders'] as $forwarder)
        {{ $forwarder }};
    @endforeach
};
@endif

/*
- If you are building an AUTHORITATIVE DNS server, do NOT enable recursion.
- If you are building a RECURSIVE (caching) DNS server, you need to enable
recursion.
- If your recursive DNS server has a public IP address, you MUST enable access
control to limit queries to your legitimate users. Failing to do so will
cause your server to become part of large scale DNS amplification
attacks. Implementing BCP38 within your network would greatly
reduce such attack surface
*/
recursion @if(isset($bind9Data['recursion'])) {{ $bind9Data['recursion']}} @else yes @endif;
allow-recursion @if(isset($bind9Data['allowRecursion'])) {{ '{' . $bind9Data['allowRecursion'] . '}' }} @else { any; }@endif;

dnssec-validation @if(isset($bind9Data['dnsValidation'])) {{ $bind9Data['dnsValidation'] }} @else auto @endif;

managed-keys-directory "/var/named/dynamic";
geoip-directory "/usr/share/GeoIP";

pid-file "/run/named/named.pid";
session-keyfile "/run/named/session.key";

/* https://fedoraproject.org/wiki/Changes/CryptoPolicy */
include "/etc/crypto-policies/back-ends/bind.config";
};

{{--logging {--}}
{{--    channel default_debug {--}}
{{--        file "/var/named/data/named.run";--}}
{{--        severity dynamic;--}}
{{--    };--}}
{{--};--}}

include "/etc/named.rfc1912.zones";
include "/etc/named.root.key";
@if(isset($bind9Data['domains']))
@foreach($bind9Data['domains'] as $domain)
include "/etc/named.{{ $domain }}.zones";
@endforeach
@endif
