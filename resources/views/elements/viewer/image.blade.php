<div id="imageViewerModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="imageModalDownload btn btn-dark mdi mdi-download">
                    <span class="sr-only">({{ __('omen::download Image') }})</span></button>
                <button type="button" class="imageModalFullscreen btn btn-dark mdi mdi-fullscreen">
                    <span class="sr-only">({{ __('omen::fullscreen') }})</span></button>
                <button type="button" class="imageModalFullscreenExit d-none btn btn-dark mdi mdi-fullscreen-exit">
                    <span class="sr-only">({{ __('omen::exit fullscreen') }})</span></button>
                <button type="button" class="btn btn-secondary edit" data-dismiss="modal">{{ __('Edit file') }}</button>
                <h5 class="ml-4"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="sr-only">({{ __('omen::close image viewer') }})</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- This div is needed for a css hack image loader --}}
                <div></div>
                <h4 class="text-center d-none">{{ __('omen::Can\'t display image, your browser does not support it, you can still download it') }}</h4>
                <img class="img-fluid border-0" src="" alt="">
            </div>
        </div>
    </div>
</div>
