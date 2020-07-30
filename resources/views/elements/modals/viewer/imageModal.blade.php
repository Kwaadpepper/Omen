<div id="imageViewerModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="imageModalDownload btn btn-dark mdi mdi-download" aria-toggle="tooltip" title="{{ __('omen::Download') }}">
                    <span class="sr-only">({{ __('omen::Download') }})</span></button>
                <button type="button" class="imageModalFullscreen btn btn-dark mdi mdi-fullscreen" aria-toggle="tooltip" title="{{ __('omen::Fullscreen') }}">
                    <span class="sr-only">({{ __('omen::Fullscreen') }})</span></button>
                <button type="button" class="imageModalFullscreenExit d-none btn btn-dark mdi mdi-fullscreen-exit" aria-toggle="tooltip" title="{{ __('omen::Exit fullscreen') }}">
                    <span class="sr-only">({{ __('omen::Exit fullscreen') }})</span></button>
                <button type="button" class="btn btn-secondary edit" data-dismiss="modal">{{ __('omen::Edit') }}</button>
                <h5 class="ml-4"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-toggle="tooltip" title="{{ __('omen::Close') }}">
                    <span class="sr-only">({{ __('omen::Close') }})</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4 class="text-center d-none">{{ __('omen::Can\'t display image, your browser does not support it, you can still download it') }}</h4>
                <img class="img-fluid border-0" src="" alt="" data-toggle="tooltip" title="{{ __('omen::Scroll to zoom, drag to move') }}">
            </div>
        </div>
    </div>
</div>
