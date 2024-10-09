@if(isset($bind9ForwardData))

$TTL {{ $bind9ForwardData['ttl'] }}
@              IN           SOA         {{ $bind9ForwardData['ns1_name'] }}.    {{ $bind9ForwardData['admin_ns'] }}. (
                                        {{ $bind9ForwardData['serial'] }}           ; Serial number
                                        {{ $bind9ForwardData['refresh'] }}          ; Refresh
                                        {{ $bind9ForwardData['retry'] }}            ; Retry
                                        {{ $bind9ForwardData['expire'] }}           ; Expire
                                        {{ $bind9ForwardData['negativeCache'] }})   ; Negative cache TTL

@if(isset($bind9ForwardData['records']))

@if(isset($bind9ForwardData['ns1_name']))
@   IN  NS  {{$bind9ForwardData['ns1_name']}}.
@endif
@if(isset($bind9ForwardData['ns2_name']))
@   IN  NS  {{$bind9ForwardData['ns2_name']}}.
@endif
@if(isset($bind9ForwardData['ns3_name']))
@   IN  NS  {{$bind9ForwardData['ns3_name']}}.
@endif
@if(isset($bind9ForwardData['ns4_name']))
@   IN  NS  {{$bind9ForwardData['ns4_name']}}.
@endif

{{$bind9ForwardData['domain']}}.    IN  A   {{$bind9ForwardData['nsIp']}}
www     {{$bind9ForwardData['ttl']}}    IN  CNAME   {{$bind9ForwardData['domain']}}.

@foreach($bind9ForwardData['records'] as $record)
@if($record['type'] === 'A')
    @php
        $aHeader = "; A records map domain names to IPv4 addresses";
        $recordA[] = "{$record['domain']}.   IN  {$record['type']}   {$record['record']}";
    @endphp
@elseif($record['type'] === 'CNAME')
    @php
        $cnameHeader = "; CNAME records alias one domain name to another";
        $recordCNAME[] = "{$record['name']}    IN  {$record['type']}   {$record['domain']}.";
    @endphp
@elseif($record['type'] === 'MX')
    @php
        $mxHeader = "; MX records define mail servers for the domain";
        $recordMX[] = "{$record['record']}.   IN  {$record['type']} {$record['priority']}     {$record['name']}";
    @endphp
@endif
@if($record['type'] === 'TXT')
    @php
        $txtHeader = "; TXT records are used to store text-based information related to the domain";
        $recordTXT[] = "{$record['domain']}.   IN  {$record['type']}   {$record['record']}";
    @endphp
@endif
@endforeach
@endif
@endif

@if(isset($aHeader) && isset($recordA))
{{$aHeader}}
@foreach($recordA as $a)
{{$a}}
@endforeach
@endif

@if(isset($cnameHeader) && isset($recordCNAME))
{{$cnameHeader}}
@foreach($recordCNAME as $cname)
{{$cname}}
@endforeach
@endif

@if(isset($mxHeader) && isset($recordMX))
{{$mxHeader}}
@foreach($recordMX as $mx)
{{$mx}}
@endforeach
@endif

@if(isset($txtHeader) && isset($recordTXT))
{{$txtHeader}}
@foreach($recordTXT as $txt)
{{$txt}}
@endforeach
@endif
