@if(!empty($hotlinkData) && $hotlinkData['enabled'] === 'enabled')

{{ $hotlinkData['rewriteEngine'] }}

@if(isset($hotlinkData['allowDirectRequests']) && $hotlinkData['allowDirectRequests'] === true)

    {{ $hotlinkData['rewriteCond'] }}

@endif

@foreach($hotlinkData['urlAllowAccess'] as $hotlink)

    {{ $hotlink }}

@endforeach

@if(isset($hotlinkData['rewriteRule']))

    {{ $hotlinkData['rewriteRule'] }}

@endif

@endif
