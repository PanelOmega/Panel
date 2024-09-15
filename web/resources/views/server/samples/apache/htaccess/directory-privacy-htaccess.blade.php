@if(isset($dPrivacyContent['protected']) && $dPrivacyContent['protected'] === true)

{{ $dPrivacyContent['authType'] }}
{{ $dPrivacyContent['authName'] }}
{{ $dPrivacyContent['authUserFile'] }}
{{ $dPrivacyContent['requireUser'] }}

@endif
