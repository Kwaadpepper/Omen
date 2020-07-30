<div id="videoViewerModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="videoModalDownload btn btn-dark mdi mdi-download" aria-toggle="tooltip" title="{{ __('omen::Download') }}">
                    <span class=" sr-only">({{ __('omen::Download') }})</span></button>
                <h5 class="ml-4"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-toggle="tooltip" title="{{ __('omen::Close') }}">
                    <span class=" sr-only">({{ __('omen::Close') }})</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="embed-responsive">
                    <video width="100%" height="100%" controls="controls" preload="none">
                        {{ __('omen::An error has occured while retrieving this file') }}
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
