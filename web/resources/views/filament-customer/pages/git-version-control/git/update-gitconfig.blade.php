[safe]
@if(isset($gitPaths))
@foreach($gitPaths as $path)
{{ $path }}
@endforeach
@endif
