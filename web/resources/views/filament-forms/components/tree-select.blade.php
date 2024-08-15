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
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/treeselectjs@0.11.0/dist/treeselectjs.css" />

        <style>
            .treeselect-list {
                padding: 10px;
            }
            .treeselect-input {
                border-radius: 6px;
            }
            .treeselect-input__edit {
                padding: 5px 10px;
            }
            .treeselect-input__tags-element {
                padding: 5px 10px;
            }

            .dark .treeselect-input__tags-element {
                background-color: #2d2d2d;
            }
            .dark .treeselect-input__tags-cross svg {
                stroke: #d8d8d9;
            }
            .dark .treeselect-input {
                border: 1px solid #4b4b4b;
                background-color: #1e1e23;
            }
            .dark .treeselect-input__edit {
                background: transparent !important;
            }
            .dark .treeselect-list {
                background-color: #1e1e23;
                border: 1px solid #4b4b4b;
            }
            .dark .treeselect-list.treeselect-list--single-select .treeselect-list__item--single-selected {
                background-color: rgba(255, 255, 255, 0.1) !important;
            }
            .dark .treeselect-list__item--focused {
                background-color: rgba(255, 255, 255, 0.1) !important;
            }

            .treeselect-list.treeselect-list--single-select .treeselect-list__item--single-selected {
                background-color: #0000000f !important;
                border-radius: 6px;
            }

            .treeselect-list__item--focused {
                background-color: #0000000f !important;
                border-radius: 6px;
            }

            .treeselect-input--focused {
                border-color: #d8d8d9 !important;;
            }

            .treeselect-list--focused {
                border-color: #d8d8d9
            }

            .treeselect-list--top, .treeselect-list--top-to-body {
                border-bottom-color: transparent;
            }

            .treeselect-list--bottom, .treeselect-list--bottom-to-body {
                border-top-color: transparent;
            }
        </style>

        <script>

            window.onload = function() {
                const options = @json($getOptions())

                const domElement = document.getElementById('js-tree-select-{{$id}}')
                const treeSelect = new Treeselect({
                    parentHtmlContainer: domElement,
                    value: [4, 7, 8],
                    options: options,
                    alwaysOpen: true,
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
