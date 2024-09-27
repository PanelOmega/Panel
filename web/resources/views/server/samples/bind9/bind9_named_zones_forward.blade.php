@if(isset($bind9ForwardData))

$TTL {{ $bind9ForwardData['ttl'] }}

@              IN           SOA         ns1.{{ $bind9ForwardData['domain'] }}. admin.{{ $bind9ForwardData['domain'] }}. (
                                        {{ $bind9ForwardData['serial'] }}           ; Serial number
                                        {{ $bind9ForwardData['refresh'] }}          ; Refresh
                                        {{ $bind9ForwardData['retry'] }}            ; Retry
                                        {{ $bind9ForwardData['expire'] }}           ; Expire
                                        {{ $bind9ForwardData['negativeCache'] }})   ; Negative cache TTL

@if(isset($bind9ForwardData['records']))

@   IN  NS  ns1.{{$bind9ForwardData['domain']}}.
@   IN  NS  ns2.{{$bind9ForwardData['domain']}}.

; A records map domain names to IPv4 addresses

ns1.{{ $bind9ForwardData['domain']  }}. IN  A  {{$bind9ForwardData['nsIp']}}
ns2.{{ $bind9ForwardData['domain']  }}. IN  A  {{$bind9ForwardData['nsIp']}}
@foreach($bind9ForwardData['records'] as $record)
@if($record['type'] === 'A')
    @php $recordA[] = "{$record['domain']}.   IN  {$record['type']}   {$record['record']}"; @endphp
@elseif($record['type'] === 'CNAME')
    @php $cnameHeader = "; CNAME records alias one domain name to another"; @endphp
    @php
//        $record['name'] = rtrim($record['name'], '.');
        $recordCNAME[] = "{$record['name']}    IN  {$record['type']}   {$record['domain']}.";
    @endphp
@elseif($record['type'] === 'MX')
    @php $mxHeader = "; MX records define mail servers for the domain"; @endphp
    @php $recordMX[] = "{$record['record']}.   IN  {$record['type']} {$record['priority']}     {$record['name']}"; @endphp
@endif
@endforeach
@endif
@endif

@if(isset($recordA))
@foreach($recordA as $a)
{{$a}}
@endforeach
@endif

@if(isset($cnameHeader))
{{$cnameHeader}}
@foreach($recordCNAME as $cname)
{{$cname}}
@endforeach
@endif

@if(isset($mxHeader))
{{$mxHeader}}
@foreach($recordMX as $mx)
{{$mx}}
@endforeach
@endif
