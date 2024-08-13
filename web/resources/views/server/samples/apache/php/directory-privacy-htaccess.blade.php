# BEGIN PanelOmega-generated handler, do not edit

@if(isset($phpVersion) && !empty($phpVersion))
    <IfModule mime_module>
        AddHandler {{$phpVersion['fileType']}} {{$phpVersion['fileExtensions']}}
    </IfModule>
@endif


@if(isset($dPrivacyContent['auth_name']) && isset($dPrivacyContent['auth_user_file']))
    AuthType Basic
    AuthName {{ $dPrivacyContent['auth_name'] }}{{ PHP_EOL }}
    AuthUserFile {{ $dPrivacyContent['auth_user_file'] }}{{ PHP_EOL }}
    Require valid-user
@endif
# END PanelOmega-generated handler, do not edit
