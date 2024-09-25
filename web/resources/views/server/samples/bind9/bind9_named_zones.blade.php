@if(isset($bind9Zones))
    @foreach($bind9Zones as $zone)

zone "{{ $zone['domain'] }}" {
    type master;
    file "/etc/named.{{ $zone['domain'] }}.db";
};

    @endforeach
@endif
