@if(isset($forwardZones))
    @foreach($forwardZones as $zone)

zone "{{ $zone['domain'] }}" {
    type master;
    file "/etc/named.{{ $zone['domain'] }}.db";
};

    @endforeach
@endif

@if(isset($reverseZones))
    @foreach($reverseZones as $zone)

zone "{{ $zone['ip'] }}.in-addr.arpa" IN {
    type master;
    file "/etc/named.{{ $zone['ip'] }}.rev";
};

    @endforeach
@endif
