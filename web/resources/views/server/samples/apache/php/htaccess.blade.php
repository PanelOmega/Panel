# BEGIN PanelOmega-generated handler, do not edit
@if(isset($phpVersion) && !empty($phpVersion))
    <IfModule mime_module>
        AddHandler {{$phpVersion['fileType']}} {{$phpVersion['fileExtensions']}}
    </IfModule>
@endif

# END PanelOmega-generated handler, do not edit
