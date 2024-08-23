@if(isset($redirectsData) && !empty($redirectsData))
    @foreach($redirectsData as $data)
        {{ $data['rewriteCond'] }} {{ PHP_EOL }}
        {{ $data['rewriteRule'] }}
    @endforeach
@endif
