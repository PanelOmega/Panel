@if(isset($bind9ReverseData))

$TTL {{ $bind9ReverseData['ttl'] }}

@              IN           SOA         ns1.{{ $bind9ReverseData['domain'] }}. admin.{{ $bind9ReverseData['domain'] }}. (
    {{ $bind9ReverseData['serial'] }}           ; Serial number
    {{ $bind9ReverseData['refresh'] }}          ; Refresh
    {{ $bind9ReverseData['retry'] }}            ; Retry
    {{ $bind9ReverseData['expire'] }}           ; Expire
    {{ $bind9ReverseData['negativeCache'] }})   ; Negative cache TTL

@if(isset($bind9ReverseData['records']))

@   IN  NS  ns1.{{ $bind9ReverseData['domain'] }}.
@   IN  NS  ns2.{{ $bind9ReverseData['domain'] }}.

{{--ns1.{{ $bind9ReverseData['domain']  }}. IN  A  {{$bind9ReverseData['nsIp']}}--}}
{{--ns2.{{ $bind9ReverseData['domain']  }}. IN  A  {{$bind9ReverseData['nsIp']}}--}}

; PTR records

{{ $bind9ReverseData['revIp'] }}.in-addr.arpa.  IN  PTR     ns1.{{ $bind9ReverseData['domain'] }}.
{{ $bind9ReverseData['revIp'] }}.in-addr.arpa.  IN  PTR     ns2.{{ $bind9ReverseData['domain'] }}.
{{ $bind9ReverseData['revIp'] }}.in-addr.arpa.  IN  PTR     {{ $bind9ReverseData['domain'] }}.

    @endif
@endif

