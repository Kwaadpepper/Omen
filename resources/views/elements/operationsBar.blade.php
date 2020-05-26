<div id="operationsToolbar" class="btn-toolbar">
    <div class="btn-group" role="group" aria-label="Basic example">
        <label id="operationsSelectAll" class="btn btn-outline-dark figCheck customCheckbox" data-toggle="tooltip" data-placement="top" title="{{ __('omen::Select all') }}">
            <input type="checkbox" name="selectAll">
            <span class="checkmark"></span>
            <span class="sr-only">({{ __('omen::Select all') }})</span>
        </label>
        <button id="operationsCopy" type="button" class="btn btn-outline-dark mdi mdi-content-copy" data-toggle="tooltip" data-placement="top" title="{{ __('omen::copy') }}">
            <span class="sr-only">({{ __('omen::copy') }})</span></button>
        <button id="operationsCut" type="button" class="btn btn-outline-dark mdi mdi-content-cut" data-toggle="tooltip" data-placement="top" title="{{ __('omen::cut') }}">
            <span class="sr-only">({{ __('omen::cut') }})</span></button>
        <button id="operationsPaste" type="button" class="btn btn-outline-dark mdi mdi-content-paste" data-toggle="tooltip" data-placement="top" title="{{ __('omen::paste') }}">
            <span class="sr-only">({{ __('omen::paste') }})</span></button>
    </div>
</div>
