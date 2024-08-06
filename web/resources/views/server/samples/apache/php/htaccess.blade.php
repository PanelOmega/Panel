# BEGIN PanelOmega-generated handler, do not edit
@if(isset($phpVersion) && !empty($phpVersion))
    <IfModule mime_module>
        AddHandler {{$phpVersion['fileType']}} {{$phpVersion['fileExtensions']}}
    </IfModule>
@endif

@if(!empty($dPrivacyContent['auth_name']) && !empty($dPrivacyContent['auth_user_file']))
    AuthType Basic {{ PHP_EOL }}
    AuthName {{ $dPrivacyContent['auth_name'] }}{{ PHP_EOL }}
    AuthUserFile {{ $dPrivacyContent['auth_user_file'] }}{{ PHP_EOL }}
    Require valid-user
@endif
@if(!empty($dPrivacyContent['hotlinkData']) && $dPrivacyContent['hotlinkData']['enabled'] === 'enabled')
    @if(!empty($dPrivacyContent['hotlinkData']['url_allow_access']))
        @foreach($dPrivacyContent['hotlinkData']['url_allow_access'] as $hotlink)
            SetEnvIfNoCase Referer "^{{ $hotlink }}" locally_linked=1
        @endforeach
    @endif
    @if(isset($dPrivacyContent['hotlinkData']['allow_direct_requests']) && $dPrivacyContent['hotlinkData']['allow_direct_requests']!== null)
        RewriteEngine On
        RewriteCond %{ENV:{{$dPrivacyContent['hotlinkData']['env']}}} !1
        RewriteRule ^ - [F]
    @endif
    @if(!empty($dPrivacyContent['hotlinkData']['block_extensions']))
        <FilesMatch ".({{str_replace(',', '|', $dPrivacyContent['hotlinkData']['block_extensions'])}})$">
        Order Allow,Deny
        Allow from env={{$dPrivacyContent['hotlinkData']['env']}}
        </FilesMatch>
    @endif
    @if(isset($dPrivacyContent['hotlinkData']['redirect_to']))
        RewriteEngine On
        RewriteCond %{ENV:{{$dPrivacyContent['hotlinkData']['env']}}} !1
        RewriteRule ^ {{ $dPrivacyContent['hotlinkData']['redirect_to'] }} [R=301,L]
    @endif
@endif

# END PanelOmega-generated handler, do not edit
