<style>
    .treeselect-input {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d8d8d9;
        border-radius: 6px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        padding: 6px 40px 6px 6px;
        position: relative;
        min-height: 37px;
        background-color: #fff;
        cursor: text
    }
    .dark .treeselect-input {
        border: 1px solid #414145;
        background-color: #1e1e23;
        color: #fff;
    }

    .treeselect-input--unsearchable {
        cursor: default
    }

    .treeselect-input--unsearchable .treeselect-input__edit {
        caret-color: transparent;
        cursor: default
    }

    .treeselect-input--unsearchable .treeselect-input__edit:focus {
        position: absolute;
        z-index: -1;
        left: 0;
        min-width: 0;
        width: 0
    }

    .treeselect-input--value-not-selected .treeselect-input__edit, .treeselect-input--value-not-selected.treeselect-input--unsearchable .treeselect-input__edit:focus {
        z-index: auto;
        position: static;
        width: 100%;
        max-width: 100%
    }

    .treeselect-input--value-not-selected .treeselect-input__tags {
        gap: 0
    }

    [dir=rtl] .treeselect-input {
        padding-right: 4px;
        padding-left: 40px
    }

    [dir=rtl] .treeselect-input__operators {
        right: unset;
        left: 2px
    }

    .treeselect-input__tags {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
        padding:5px;
    }

    .treeselect-input__tags-element {
        display: inline-flex;
        align-items: center;
        background-color: #e16449;
        color: #fff;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 6px;
        font-size: 14px;
        max-width: 100%;
        box-sizing: border-box
    }

    .treeselect-input__tags-element:hover {
        background-color: #c5c7cb
    }

    .treeselect-input__tags-cross svg {
        stroke: #fff !important;
    }

    .treeselect-input__tags-element:hover .treeselect-input__tags-cross svg {
        stroke: #fff !important;
    }

    .treeselect-input__tags-name {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis
    }

    .treeselect-input__tags-cross {
        display: flex;
        margin-left: 2px
    }

    .treeselect-input__tags-cross svg {
        width: 12px;
        height: 12px
    }

    .treeselect-input__tags-count {
        font-size: 14px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis
    }

    .treeselect-input__edit {
        flex: 1;
        border: none;
        font-size: 14px;
        text-overflow: ellipsis;
        width: 100%;
        max-width: calc(100% - 45px);
        padding: 0;
        position: absolute;
        z-index: -1;
        min-width: 0;
        background: transparent !important;
    }

    .treeselect-input__edit:focus {
        outline: none !important;
        min-width: 30px;
        max-width: 100%;
        z-index: auto;
        position: static
    }

    .treeselect-input__operators {
        display: flex;
        max-width: 40px;
        position: absolute;
        right: 8px;
    }

    .treeselect-input__clear {
        display: flex;
        cursor: pointer
    }

    .treeselect-input__clear svg {
        stroke: #fff;
        width: 17px;
        min-width: 17px;
        height: 20px
    }

    .treeselect-input__clear:hover svg {
        stroke: #838790
    }

    .treeselect-input__arrow {
        display: flex;
        cursor: pointer
    }

    .treeselect-input__arrow svg {
        stroke: #c5c7cb;
        width: 20px;
        min-width: 20px;
        height: 20px
    }

    .treeselect-input__arrow:hover svg {
        stroke: #838790
    }

    .treeselect-list {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d8d8d9;
        overflow-y: auto;
        background-color: #fff;
        max-height: 300px
    }
    .dark .treeselect-list {
        border: 1px solid #414145;
        background-color: #1e1e23;
        color: #fff;
    }

    .treeselect-list__group-container {
        box-sizing: border-box
    }

    .treeselect-list__item {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        cursor: pointer;
        height: 30px
    }

    .treeselect-list__item:focus {
        outline: none
    }

    .treeselect-list__item--focused {
        background-color: #e164491a !important;
        border-radius: 6px;
    }

    .treeselect-list__item--hidden {
        display: none
    }

    .treeselect-list__item-icon {
        display: flex;
        align-items: center;
        cursor: pointer;
        height: 20px;
        width: 20px;
        min-width: 20px
    }

    .treeselect-list__item-icon svg {
        pointer-events: none;
        width: 100%;
        height: 100%;
        stroke: #c5c7cb
    }

    .treeselect-list__item-icon * {
        pointer-events: none
    }

    .treeselect-list__item-icon:hover svg {
        stroke: #838790
    }

    .treeselect-list__item-checkbox-container {
        width: 20px;
        height: 20px;
        min-width: 20px;
        border: 1px solid #d8d8d9;
        border-radius: 3px;
        position: relative;
        background-color: #fff;
        pointer-events: none;
        box-sizing: border-box
    }

    .treeselect-list__item-checkbox-container svg {
        position: absolute;
        height: 100%;
        width: 100%
    }

    .treeselect-list__item-checkbox {
        margin: 0;
        width: 0;
        height: 0;
        pointer-events: none;
        position: absolute;
        z-index: -1
    }

    .treeselect-list__item-checkbox-icon {
        position: absolute;
        height: 100%;
        width: 100%;
        left: 0;
        top: 0;
        text-align: left
    }

    .treeselect-list__item-label {
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: keep-all;
        white-space: nowrap;
        font-size: 14px;
        padding-left: 5px;
        pointer-events: none;
        text-align: left
    }

    .treeselect-list__item-label-counter {
        margin-left: 3px;
        color: #838790;
        font-size: 13px
    }

    .treeselect-list__empty {
        display: flex;
        align-items: center;
        height: 30px;
        padding-left: 4px
    }

    .treeselect-list__empty--hidden {
        display: none
    }

    .treeselect-list__empty-icon {
        display: flex;
        align-items: center
    }

    .treeselect-list__empty-text {
        font-size: 14px;
        padding-left: 5px;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: keep-all;
        white-space: nowrap
    }

    .treeselect-list__slot {
        position: sticky;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        bottom: 0;
        background-color: #fff
    }

    .treeselect-list.treeselect-list--single-select .treeselect-list__item-checkbox-container, .treeselect-list.treeselect-list--disabled-branch-node .treeselect-list__item--group .treeselect-list__item-checkbox-container {
        display: none
    }

    .treeselect-list__item--checked {
        background-color: #d8d8d9
    }

    .treeselect-list.treeselect-list--single-select .treeselect-list__item--checked {
        background-color: transparent
    }

    .treeselect-list.treeselect-list--single-select .treeselect-list__item--single-selected {
        background-color: #e16449;
        color: #fff;
        border-radius: 6px;
    }

    .treeselect-list__item .treeselect-list__item-checkbox-container svg {
        stroke: transparent
    }

    .treeselect-list__item--checked .treeselect-list__item-checkbox-container svg, .treeselect-list__item--partial-checked .treeselect-list__item-checkbox-container svg {
        stroke: #fff
    }

    .treeselect-list__item--checked .treeselect-list__item-checkbox-container, .treeselect-list__item--partial-checked .treeselect-list__item-checkbox-container {
        background-color: #52c67e
    }

    .treeselect-list__item--disabled .treeselect-list__item-checkbox-container {
        background-color: #d8d8d9
    }

    .treeselect-list__item--disabled .treeselect-list__item-label {
        color: #c5c7cb
    }

    [dir=rtl] .treeselect-list__item-checkbox-icon {
        text-align: right
    }

    [dir=rtl] .treeselect-list__item-label {
        text-align: right;
        padding-right: 5px;
        padding-left: unset
    }

    [dir=rtl] .treeselect-list__item--closed .treeselect-list__item-icon {
        transform: rotate(180deg)
    }

    [dir=rtl] .treeselect-list__empty {
        padding-right: 4px;
        padding-left: unset
    }

    [dir=rtl] .treeselect-list__empty-text {
        padding-right: 5px;
        padding-left: unset
    }

    .treeselect {
        width: 100%;
        position: relative;
        box-sizing: border-box
    }

    .treeselect--disabled {
        pointer-events: none
    }

    .treeselect-list {
        position: absolute;
        left: 0;
        border-radius: 6px;
        box-sizing: border-box;
        z-index: 1000;
        padding: 10px;
    }

    .treeselect .treeselect-list {
        position: absolute
    }

    .treeselect .treeselect-list--static {
        position: static
    }

    .treeselect-input--focused {

    }

    .treeselect-input--opened.treeselect-input--top {
        border-top-color: transparent;
        border-top-left-radius: 0;
        border-top-right-radius: 0
    }

    .treeselect-input--opened.treeselect-input--bottom {
        border-bottom-color: transparent;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0
    }

    .treeselect-list--focused {
        border-color: #d8d8d9;
    }

    .treeselect-list--top, .treeselect-list--top-to-body {
        border-bottom-color: #d8d8d9;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0
    }

    .treeselect-list--bottom, .treeselect-list--bottom-to-body {
        border-top-color: #d8d8d9;
        border-top-left-radius: 0;
        border-top-right-radius: 0
    }

    .treeselect-list--top {
        left: 0;
        bottom: 100%
    }

    .treeselect-list--bottom {
        left: 0;
        top: 100%
    }

</style>
