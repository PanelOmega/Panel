@if(isset($dPrivacyContent['protected']) && $dPrivacyContent['protected'] === true)
    AuthType Basic
    AuthName {{ $dPrivacyContent['auth_name'] }}
    AuthUserFile {{ $dPrivacyContent['auth_user_file'] }}
    Require valid-user
@endif
