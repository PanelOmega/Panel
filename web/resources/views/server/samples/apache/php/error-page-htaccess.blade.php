@if(isset($errorCodes) && !empty($errorCodes))
    @foreach($errorCodes as $error)
        ErrorDocument {{ $error }} @if(!empty($errorPagePath)) {{ $errorPagePath }}/{{ $error }}.shtml @endif {{ PHP_EOL }}
    @endforeach
@endif
