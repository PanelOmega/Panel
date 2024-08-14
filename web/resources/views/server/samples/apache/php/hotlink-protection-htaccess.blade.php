@if(!empty($hotlinkData) && $hotlinkData['enabled'] === 'enabled')
    RewriteEngine on
    @if(isset($hotlinkData['allow_direct_requests']) && $hotlinkData['allow_direct_requests'] === true)
        RewriteCond %{HTTP_REFERER} !^$
    @endif
    @if(isset($hotlinkData['url_allow_access']))
        @foreach($hotlinkData['url_allow_access'] as $hotlink)
            RewriteCond %{HTTP_REFERER} !^{{ $hotlink['protocol'] }}://({{ $hotlink['subdomain'] ?? 'www' }}\.)?{{ $hotlink['domain'] }}/.*$ [NC]
        @endforeach
    @endif
    @if(isset($hotlinkData['block_extensions']) && $hotlinkData['block_extensions'] !== '')
        RewriteRule .*\.({{ str_replace(',', '|', trim($hotlinkData['block_extensions'], ',')) }})$ @if(isset($hotlinkData['redirect_to'])){{ $hotlinkData['redirect_to'] }}@endif - [R,NC]
    @endif
@endif
