<div id="textViewerModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="textModalDownload btn btn-dark mdi mdi-download">
                    <span class="sr-only">({{ __('omen::download File') }})</span></button>
                <button type="button" class="textModalFullscreen btn btn-dark mdi mdi-fullscreen">
                    <span class="sr-only">({{ __('omen::fullscreen') }})</span></button>
                <button type="button" class="textModalFullscreenExit d-none btn btn-dark mdi mdi-fullscreen-exit">
                    <span class="sr-only">({{ __('omen::exit fullscreen') }})</span></button>
                <h5 class="ml-4"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="sr-only">({{ __('omen::close text viewer') }})</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4 class="text-center d-none">{{ __('omen::An error has occured while retrieving this file') }}</h4>
                <div class="container-fluid">
                    <pre></pre>
                </div>
            </div>
            <div class="modal-footer">
                {{-- Maybe implement a text editor ? --}}
                {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
