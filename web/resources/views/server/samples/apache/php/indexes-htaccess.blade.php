@if(isset($index) && !empty($index))
    Options {{ $index['Indexes'] }}Indexes
    @if(isset($index['HTMLTable']) && isset($index['FancyIndexing']))
    IndexOptions {{ $index['HTMLTable'] }}HTMLTable {{ $index['FancyIndexing'] }}FancyIndexing
    @endif
@endif
