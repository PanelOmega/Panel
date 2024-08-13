@if(isset($dPrivacyContent) && !empty($dPrivacyContent))
    @foreach($dPrivacyContent as $content)
        {{ $content }}
    @endforeach
@endif
