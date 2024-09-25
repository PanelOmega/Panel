@if(isset($bind9ForwardData))

$TTL {{ $bind9ForwardData['ttl'] }}

@              IN           SOA         ns1.{{ $bind9ForwardData['domain'] }}. admin.{{ $bind9ForwardData['domain'] }}. (
                                        {{ $bind9ForwardData['serial'] }}           ; Serial number
                                        {{ $bind9ForwardData['refresh'] }}          ; Refresh
                                        {{ $bind9ForwardData['retry'] }}            ; Retry
                                        {{ $bind9ForwardData['expire'] }}           ; Expire
                                        {{ $bind9ForwardData['negativeCache'] }})   ; Negative cache TTL

@if(isset($bind9ForwardData['records']))

        @php
            $commentAdded = [
                'A' => false,
                'MX' => false,
                'CNAME' => false
            ];
        @endphp

@   IN  NS  ns1.{{$bind9ForwardData['domain']}}.

@foreach($bind9ForwardData['records'] as $record)

@if($record['type'] === 'A')
                @if(!$commentAdded['A'])

; A records map domain names to IPv4 addresses

                    @php $commentAdded['A'] = true @endphp
                @endif

@   IN  {{ $record['type'] }}   {{ $record['record'] }}

            @elseif($record['type'] === 'CNAME')
                @if(!$commentAdded['CNAME'])

; CNAME records alias one domain name to another

                    @php $commentAdded['CNAME'] = true @endphp
                @endif

{{$record['name']}}   IN  {{ $record['type'] }}   {{ $record['record'] }}.

@elseif($record['type'] === 'MX')
                @if(!$commentAdded['MX'])

; MX records define mail servers for the domain

                    @php $commentAdded['MX'] = true @endphp

                @endif

@   IN  {{ $record['type'] }} {{ $record['priority'] }}     ns1.{{ $record['domain'] }}

            @endif
        @endforeach
    @endif
@endif
