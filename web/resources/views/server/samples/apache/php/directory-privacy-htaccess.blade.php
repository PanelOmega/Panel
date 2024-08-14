@if(isset($dPrivacyContent['auth_name']) && isset($dPrivacyContent['auth_user_file']))
    AuthType Basic
    AuthName {{ $dPrivacyContent['auth_name'] }}
    AuthUserFile {{ $dPrivacyContent['auth_user_file'] }}
    Require valid-user
@endif
