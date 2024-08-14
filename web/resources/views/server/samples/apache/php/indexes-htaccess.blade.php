@if(isset($index) && !empty($index))
    Options {{ $index['indexes'] }}
{{--    +Indexes--}}
    IndexOptions {{ $index['html_table'] }} {{ $index['fancy_indexing'] }}
{{--    -HTMLTable -FancyIndexing--}}

@endif
