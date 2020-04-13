<div id="pdfViewerModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="pdfModalDownload btn btn-dark mdi mdi-download">
                    <span class="sr-only">({{ __('omen::download PDF') }})</span></button>
                <button type="button" class="pdfModalFullscreen btn btn-dark mdi mdi-fullscreen">
                    <span class="sr-only">({{ __('omen::fullscreen') }})</span></button>
                <button type="button" class="pdfModalFullscreenExit d-none btn btn-dark mdi mdi-fullscreen-exit">
                    <span class="sr-only">({{ __('omen::exit fullscreen') }})</span></button>
                <h5 class="ml-4"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="sr-only">({{ __('omen::close pdf viewer') }})</span>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4 class="text-center d-none">{{ __('omen::An error has occured while retrieving this file') }}</h4>
                <iframe id="pdfViewerModalIFrame" src="about:blank" data-csp="{{ config('omen.cspToken') }}" data-script-pdf="{{ asset(sprintf('%s/js/vendor/pdf.min.js', $assetPath)) }}" data-script-worker="{{ asset(sprintf('%s/js/vendor/pdf.worker.min.js', $assetPath)) }}" data-script-css="{{ asset(sprintf('%s/css/omenPdf.css', $assetPath)) }}" data-script-css-bg="{{ asset(sprintf('%s/images/whitenoise-361x370.png', $assetPath)) }}" data-script-js-web="{{ asset(sprintf('%s/js/vendor/pdf.viewer.min.js', $assetPath)) }}" data-script-css-web="{{ asset(sprintf('%s/js/vendor/pdf.viewer.min.css', $assetPath)) }}"></iframe>
            </div>
        </div>
    </div>
</div>
