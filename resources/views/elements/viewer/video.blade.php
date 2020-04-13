<div id="videoViewerModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="videoModalDownload btn btn-dark mdi mdi-download">
                    <span class="sr-only">({{ __('omen::download Video') }})</span></button>
                <h5 class="ml-4"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="sr-only">({{ __('omen::close video viewer') }})</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="embed-responsive">
                    <video width="100%" height="100%" controls="controls" preload="none">
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
