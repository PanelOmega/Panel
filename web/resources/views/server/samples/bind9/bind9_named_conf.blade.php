//
// named.conf
//
// Provided by Red Hat bind package to configure the ISC BIND named(8) DNS
// server as a caching only nameserver (as a localhost DNS resolver only).
//
// See /usr/share/doc/bind*/sample/ for example named configuration files.
//

options {
/* make named use port 53 for the source of all queries, to allow
* firewalls to block all ports except 53:
*/

// query-source    port 53;

recursion no;

/* We no longer enable this by default as the dns posion exploit
has forced many providers to open up their firewalls a bit */

// Put files that named is allowed to write in the data/ directory:
directory                "/var/named"; // the default
pid-file                 "/var/run/named/named.pid";
dump-file                "data/cache_dump.db";
statistics-file          "data/named_stats.txt";
/* memstatistics-file     "data/named_mem_stats.txt"; */
allow-transfer    { "none"; };

};

{{--logging {--}}
{{--category notify { zone_transfer_log; };--}}
{{--category xfer-in { zone_transfer_log; };--}}
{{--category xfer-out { zone_transfer_log; };--}}
{{--channel zone_transfer_log {--}}
{{--file "/var/named/log/default.log" versions 10 size 50m;--}}
{{--print-time yes;--}}
{{--print-category yes;--}}
{{--print-severity yes;--}}
{{--severity info;--}}
{{--};--}}
{{--};--}}

{{--@if(isset($bind9Data['service']) && $bind9Data['service'] === 'named')--}}
view "localhost_resolver" {
/* This view sets up named to be a localhost resolver ( caching only nameserver ).
* If all you want is a caching-only nameserver, then you need only define this view:
*/
match-clients         { 127.0.0.0/24; };
match-destinations    { localhost; };
recursion yes;

zone "." IN {
type hint;
file "/var/named/named.ca";
};

/* these are zones that contain definitions for all the localhost
* names and addresses, as recommended in RFC1912 - these names should
* ONLY be served to localhost clients:
*/
include "/var/named/named.rfc1912.zones";
};

view "internal" {
/* This view will contain zones you want to serve only to "internal" clients
that connect via your directly attached LAN interfaces - "localnets" .
*/
match-clients        { localnets; };
match-destinations    { localnets; };
recursion yes;

zone "." IN {
type hint;
file "/var/named/named.ca";
};

// include "/var/named/named.rfc1912.zones";
// you should not serve your rfc1912 names to non-localhost clients.

// These are your "authoritative" internal zones, and would probably
// also be included in the "localhost_resolver" view above :

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
};

    @endforeach
@endif

};

view    "external" {
/* This view will contain zones you want to serve only to "external" clients
* that have addresses that are not on your directly attached LAN interface subnets:
*/
recursion no;

// you'd probably want to deny recursion to external clients, so you don't
// end up providing free DNS service to all takers

// all views must contain the root hints zone:

zone "." IN {
type hint;
file "/var/named/named.ca";
};

// These are your "authoritative" external zones, and would probably
// contain entries for just your web and mail servers:

// BEGIN external zone entries

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
};

    @endforeach
@endif

};

{{--@else--}}
{{--    @if(isset($bind9Data['forwardZones']))--}}
{{--        @foreach($bind9Data['forwardZones'] as $zone)--}}

{{--zone "{{ $zone['domain'] }}" {--}}
{{--    type master;--}}
{{--    file "/var/named/{{ $zone['domain'] }}.db";--}}
{{--};--}}

{{--        @endforeach--}}
{{--    @endif--}}

{{--    @if(isset($bind9Data['reverseZones']))--}}
{{--        @foreach($bind9Data['reverseZones'] as $zone)--}}

{{--zone "{{ $zone['ip'] }}.in-addr.arpa" IN {--}}
{{--    type master;--}}
{{--    file "/var/named/{{ $zone['ip'] }}.rev";--}}
{{--};--}}

{{--        @endforeach--}}
{{--    @endif--}}
{{--@endif--}}
{{--include "/etc/named.root.key";--}}
{{--include "/etc/named.rfc1912.zones";--}}
