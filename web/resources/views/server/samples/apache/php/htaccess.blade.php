# BEGIN PanelOmega-generated handler, do not edit
@if(isset($phpVersion) && !empty($phpVersion))
    <IfModule mime_module>
        AddHandler {{$phpVersion['fileType']}} {{$phpVersion['fileExtensions']}}
    </IfModule>
@endif

AuthType @if(isset($dPrivacyContent['aut_type'])) {{ $dPrivacyContent['aut_type'] }} @else Basic @endif
AuthName @if(isset($dPrivacyContent['auth_name'])) {{ $dPrivacyContent['auth_name'] }} @else @endif
AuthUserFile @if(isset($dPrivacyContent['auth_user_file'])) {{ $dPrivacyContent['auth_user_file'] }} @else @endif
Require @if(isset($dPrivacyContent['require'])) {{ $dPrivacyContent['require'] }} @else valid-user @endif

# php -- END PanelOmega-generated handler, do not edit
