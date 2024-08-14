@if(isset($phpVersion) && !empty($phpVersion))
    <IfModule mime_module>
        AddHandler {{$phpVersion['fileType']}} {{$phpVersion['fileExtensions']}}
    </IfModule>
@endif
