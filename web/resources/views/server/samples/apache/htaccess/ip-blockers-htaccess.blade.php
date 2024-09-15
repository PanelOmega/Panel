@if(isset($blockedIps) && !empty($blockedIps))

@foreach($blockedIps as $ip)

{{ $ip }}

@endforeach

@endif
