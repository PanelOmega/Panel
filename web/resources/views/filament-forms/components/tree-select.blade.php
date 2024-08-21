@php
    $statePath = $getStatePath();
    $id = $getId();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
<div
    x-data="{
        state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
    }"
>



    <input type="hidden" id="js-tree-select-hidden-{{$id}}" x-model="state" />

    <div wire:ignore>

        <script src="//cdn.jsdelivr.net/npm/treeselectjs@0.11.0/dist/treeselectjs.umd.js"></script>

        @include('filament-forms.components.tree-select-css')

        <script>

            window.onload = function() {
                const options = @json($getOptions())

                const domElement = document.getElementById('js-tree-select-{{$id}}')
                const treeSelect = new Treeselect({
                    parentHtmlContainer: domElement,
                    value: [domElement.value],
                    options: options,
                    isSingleSelect: true,
                })

                treeSelect.srcElement.addEventListener('input', (e) => {
                    console.log('Selected value:', e.detail)

                    const hiddenInput = document.getElementById('js-tree-select-hidden-{{$id}}')
                    hiddenInput.value = e.detail

                    // trigger change event
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }))


                })

            }

        </script>

        <div id="js-tree-select-{{$id}}"></div>

    </div>

</div>

</x-dynamic-component>
