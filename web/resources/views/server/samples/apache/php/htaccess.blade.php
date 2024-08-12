# BEGIN PanelOmega-generated handler, do not edit
@if(isset($phpVersion) && !empty($phpVersion))
    <IfModule mime_module>
        AddHandler {{$phpVersion['fileType']}} {{$phpVersion['fileExtensions']}}
    </IfModule>
@endif

@if(isset($dPrivacyContent['auth_name']) && isset($dPrivacyContent['auth_user_file']))
    AuthType Basic {{ PHP_EOL }}
    AuthName {{ $dPrivacyContent['auth_name'] }}{{ PHP_EOL }}
    AuthUserFile {{ $dPrivacyContent['auth_user_file'] }}{{ PHP_EOL }}
    Require valid-user
@endif

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
    @if(isset($dPrivacyContent['hotlinkData']['block_extensions']))
        RewriteRule .*\.({{ str_replace(',', '|', trim($dPrivacyContent['hotlinkData']['block_extensions'], ',')) }})$ - [NC,R,L]
    @endif
    @if(isset($dPrivacyContent['hotlinkData']['redirect_to']))
        RewriteRule ^$ {{ $dPrivacyContent['hotlinkData']['redirect_to'] }} [R,L]
    @endif
@endif

# END PanelOmega-generated handler, do not edit
