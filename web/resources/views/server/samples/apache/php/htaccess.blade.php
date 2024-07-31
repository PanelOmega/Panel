# BEGIN PanelOmega-generated handler, do not edit
@if(isset($phpVersion) && !empty($phpVersion))
    <IfModule mime_module>
        AddHandler {{$phpVersion['fileType']}} {{$phpVersion['fileExtensions']}}
    </IfModule>
@endif

AuthType Basic {{ PHP_EOL }}
AuthName @if(isset($dPrivacyContent['auth_name'])) {{ $dPrivacyContent['auth_name'] }} @else @endif {{ PHP_EOL }}
AuthUserFile @if(isset($dPrivacyContent['auth_user_file'])) {{ $dPrivacyContent['auth_user_file'] }} @else @endif {{ PHP_EOL }}
Require valid-user

# END PanelOmega-generated handler, do not edit
