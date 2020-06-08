<div class="modal fade" id="imageEditorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-90" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="ml-4">Image Name</h5>
                <button id="imageEditorCrop" type="button" class="btn btn-dark mdi mdi-crop" aria-label="{{ __('omen::Crop') }}">{{ __('omen::Crop') }}</button>
                <button id="imageEditorResize" type="button" class="btn btn-dark mdi mdi-resize" aria-label="{{ __('omen::Resize') }}">{{ __('omen::Resize') }}</button>
                <button id="imageEditorReset" type="button" class="btn btn-dark mdi mdi-reset" aria-label="{{ __('omen::Reset') }}">{{ __('omen::Reset') }}</button>
                <button id="imageEditorSave" type="button" class="btn btn-dark mdi mdi-content-save" aria-label="{{ __('omen::Save') }}">{{_('omen::Save') }}</button>
                <button id="imageEditorSaveNew" type="button" class="btn btn-dark mdi mdi-content-save" aria-label="{{ __('omen::Save as copy') }}">{{_('omen::Save as copy') }}</button>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{__('omen::Close')}}">
                    <span class="sr-only">{{ __('omen::close image editor') }}</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container col-12">
                    <div id="imageCropper" class="row">
                        <div class="col-8">
                            <img class="img-fluid border-0" src="#" alt="Image Crop Editor">
                        </div>
                        <div class="col-4">
                            <div id="imageEditorCropModeInputGroup" class="input-group mb-3">
                                <button class="btn btn-outline-secondary mdi mdi-crop active" type="button" aria-label="{{ __('omen::Set to crop mode') }}">
                                    <span class="sr-only">({{ __('omen::Set to crop mode') }})</span></button>
                                <button class="btn btn-outline-secondary mdi mdi-arrow-all" type="button" aria-label="{{ __('omen::Set to move mode') }}">
                                    <span class="sr-only">({{ __('omen::Set to move mode') }})</span></button>
                            </div>
                            <div class="input-group mb-3">
                                <button id="imageEditorFlipV" type="button" class="btn btn-dark mdi mdi-flip-horizontal" aria-label="{{ __('omen::Vertical flip') }}">{{ __('omen::Vertical flip') }}</button>
                                <button id="imageEditorFlipH" type="button" class="btn btn-dark mdi mdi-flip-vertical" aria-label="{{ __('omen::Horizontal flip') }}">{{ __('omen::Horizontal flip') }}</button>
                            </div>
                            <div id="imageEditorZoomInputGroup" class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary mdi mdi-minus" type="button" aria-label="{{ __('omen::Zoom out') }}">
                                        <span class="sr-only">({{ __('omen::Zoom out') }})</span></button>
                                </div>
                                <input type="number" class="form-control col-2" placeholder="100" step="1" aria-label="{{ __('omen::The image zoom percent') }}">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary mdi mdi-plus" type="button" aria-label="{{ __('omen::Zoom in') }}">
                                        <span class="sr-only">({{ __('omen::Zoom in') }})</span></button>
                                </div>
                            </div>
                            <div id="imageEditorRotateInputGroup" class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary mdi mdi-rotate-left" type="button" aria-label="{{ __('omen::Rotate left') }}">
                                        <span class="sr-only">({{ __('omen::Rotate left') }})</span></button>
                                </div>
                                <input type="number" class="form-control col-2" placeholder="45" step="1" aria-label="{{ __('omen::The image rotation angle') }}">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary mdi mdi-rotate-right" type="button" aria-label="{{ __('omen::Rotate right') }}">
                                        <span class="sr-only">({{ __('omen::Rotate right') }})</span></button>
                                </div>
                            </div>
                            <div id="imageEditorRatioInputGroup" class="input-group-btn" data-toggle="buttons">
                                <label class="btn btn-primary active">
                                    <input type="radio" name="options" value="0" autocomplete="off" checked>{{ __('omen::Free') }}
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" name="options" value="1" autocomplete="off">1:1
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" name="options" value="1.7777777777777777" autocomplete="off">16:9
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" name="options" value="1.6" autocomplete="off">16:10
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" name="options" value="1.3333333333333333" autocomplete="off">4:3
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" name="options" value="0.6666666666666666" autocomplete="off">2:3
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="imageResizer" class="row">
                        <div class="col-8 resizebox">
                            <img class="img-fluid border-0" src="#" alt="Image Resize Editor">
                        </div>
                        <div class="col-4">
                            <div class="row">
                                <span>{{ __('Height') }}</span>
                                <span id="imageResizerHeightOriginal"></span>
                            </div>
                            <div class="row">
                                <span>{{ __('Width') }}</span>
                                <span id="imageResizerWidthOriginal"></span>
                            </div>
                            <div class="row">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <label>{{ __('Resized height') }}</label>
                                    </div>
                                    <input id="imageResizerHeightInput" type="number" class="form-control col-2" placeholder="100" step="1">
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <label>{{ __('Resized width') }}</label>
                                    </div>
                                    <input id="imageResizerWidthInput" type="number" class="form-control col-2" placeholder="80" step="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
