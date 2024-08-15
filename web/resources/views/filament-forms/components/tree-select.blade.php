@php
    $statePath = $getStatePath();
    $id = $getId();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
<div>

    <script src="//cdn.jsdelivr.net/npm/treeselectjs@0.11.0/dist/treeselectjs.umd.js"></script>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/treeselectjs@0.11.0/dist/treeselectjs.css" />

    <script>

        window.onload = function() {
            const options = @json($getOptions())

            const domElement = document.getElementById('js-tree-select-{{$id}}')
            const treeSelect = new Treeselect({
                parentHtmlContainer: domElement,
                value: [4, 7, 8],
                options: options,
                isSingleSelect: true,
            })

            treeSelect.srcElement.addEventListener('input', (e) => {
                console.log('Selected value:', e.detail)
            })

        }

    </script>

    <div id="js-tree-select-{{$id}}"></div>

</div>

</x-dynamic-component>
