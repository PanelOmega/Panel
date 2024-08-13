@if(!empty($dPrivacyContent['hotlinkData']) && $dPrivacyContent['hotlinkData']['enabled'] === 'enabled')
    RewriteEngine on
    @if(isset($dPrivacyContent['hotlinkData']['allow_direct_requests']) && $dPrivacyContent['hotlinkData']['allow_direct_requests'] === true)
        RewriteCond %{HTTP_REFERER} !^$
    @endif
    @if(isset($dPrivacyContent['hotlinkData']['url_allow_access']))
        @foreach($dPrivacyContent['hotlinkData']['url_allow_access'] as $hotlink)
            RewriteCond %{HTTP_REFERER} !^{{ $hotlink['protocol'] }}://({{ $hotlink['subdomain'] ?? 'www' }}\.)?{{ $hotlink['domain'] }}/.*$ [NC]
        @endforeach
    @endif
    @if(isset($dPrivacyContent['hotlinkData']['block_extensions']) && $dPrivacyContent['hotlinkData']['block_extensions'] !== '')
        RewriteRule .*\.({{ str_replace(',', '|', trim($dPrivacyContent['hotlinkData']['block_extensions'], ',')) }})$ @if(isset($dPrivacyContent['hotlinkData']['redirect_to'])){{ $dPrivacyContent['hotlinkData']['redirect_to'] }}@endif - [R,NC]
    @endif
@endif
