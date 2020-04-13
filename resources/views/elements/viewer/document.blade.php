<div id="documentViewerModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="documentModalDownload btn btn-dark mdi mdi-download">
                    <span class="sr-only">({{ __('omen::download Document') }})</span></button>
                <button type="button" class="documentModalFullscreen btn btn-dark mdi mdi-fullscreen">
                    <span class="sr-only">({{ __('omen::fullscreen') }})</span></button>
                <button type="button" class="documentModalFullscreenExit d-none btn btn-dark mdi mdi-fullscreen-exit">
                    <span class="sr-only">({{ __('omen::exit fullscreen') }})</span></button>
                <h5 class="ml-4"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="sr-only">({{ __('omen::close document viewer') }})</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4 class="text-center d-none">{{ __('omen::An error has occured displaying this document') }}</h4>
                <iframe id="documentViewerModalIFrame" src="about:blank"></iframe>
            </div>
        </div>
    </div>
</div>
