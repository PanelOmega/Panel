@if(isset($htPasswdRecords) && !empty($htPasswdRecords))
    @foreach($htPasswdRecords as $record)
        {{ $record }}
    @endforeach
@endif
