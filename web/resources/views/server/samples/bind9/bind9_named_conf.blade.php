//
// named.conf
//
// Provided by Red Hat bind package to configure the ISC BIND named(8) DNS
// server as a caching only nameserver (as a localhost DNS resolver only).
//
// See /usr/share/doc/bind*/sample/ for example named configuration files.
//

options {
listen-on port 53 @if(isset($bind9Data['portV4Ips'])) {{ '{ ' . $bind9Data['portV4Ips'] . ' }' }} @else { } @endif;
listen-on-v6 port 53 @if(isset($bind9Data['portV6Ips'])) {{ '{ ' . $bind9Data['portV6Ips'] . ' }' }} @else { } @endif;
directory       "/var/named";
dump-file       "/var/named/data/cache_dump.db";
statistics-file "/var/named/data/named_stats.txt";
memstatistics-file "/var/named/data/named_mem_stats.txt";
secroots-file   "/var/named/data/named.secroots";
recursing-file  "/var/named/data/named.recursing";
allow-query @if(isset($bind9Data['allowQuery'])) {{ '{' . $bin9Data['allowQuery'] . '}' }} @else { any; } @endif;

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
dnssec-validation @if(isset($bind9Data['dnsValidation'])) {{ $bind9Data['dnsValidation'] }} @else auto @endif;

managed-keys-directory "/var/named/dynamic";
geoip-directory "/usr/share/GeoIP";

pid-file "/run/named/named.pid";
session-keyfile "/run/named/session.key";

/* https://fedoraproject.org/wiki/Changes/CryptoPolicy */
include "/etc/crypto-policies/back-ends/bind.config";
};

@if(isset($bind9Data['forwardZones']))
    @foreach($bind9Data['forwardZones'] as $zone)

zone "{{ $zone['domain'] }}" {
   type master;
   file "/var/named/{{ $zone['domain'] }}.db";
};

    @endforeach
@endif

@if(isset($bind9Data['reverseZones']))
    @foreach($bind9Data['reverseZones'] as $zone)

zone "{{ $zone['ip'] }}.in-addr.arpa" IN {
   type master;
   file "/var/named/{{ $zone['ip'] }}.rev";
;

    @endforeach
@endif

include "/etc/named.root.key";
include "/etc/named.rfc1912.zones";
