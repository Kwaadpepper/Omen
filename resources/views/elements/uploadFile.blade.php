<div id="uploadModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form id="uploadForm" method="post" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="ml-4">{{ __('Upload files') }}</h5>
                    <div>
                        <button type="button" class="btn btn-secondary pauseBtnUpload mdi mdi-pause">&nbsp;{{ __('Pause') }}</button>
                        <button type="button" class="btn btn-secondary resumeBtnUpload mdi mdi-play">&nbsp;{{ __('Resume') }}</button>
                        <button type="button" class="btn btn-secondary cancelBtnUpload mdi mdi-cancel">&nbsp;{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-secondary clearBtnUpload mdi mdi-delete">&nbsp;{{ __('Clear') }}</button>
                        <button type="button" class="btn btn-primary browseBtnUpload mdi mdi-folder-open">&nbsp;{{ __('Choose') }}...</button>
                        <button type="submit" class="btn btn-primary uploadBtnUpload mdi mdi-upload">&nbsp;{{ __('Upload') }}</button>
                        <button type="button" class="btn btn-secondary mdi mdi-close" data-dismiss="modal">&nbsp;{{ __('Close') }}</button> </div>
                </div>
                <div class="modal-body">
                    <input id="uploadInput" type="file" name="files[]" multiple="multiple">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary pauseBtnUpload mdi mdi-pause">&nbsp;{{ __('Pause') }}</button>
                    <button type="button" class="btn btn-secondary resumeBtnUpload mdi mdi-play">&nbsp;{{ __('Resume') }}</button>
                    <button type="button" class="btn btn-secondary cancelBtnUpload mdi mdi-cancel">&nbsp;{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-secondary clearBtnUpload mdi mdi-delete">&nbsp;{{ __('Clear') }}</button>
                    <button type="button" class="btn btn-primary browseBtnUpload mdi mdi-folder-open">&nbsp;{{ __('Choose') }}...</button>
                    <button type="submit" class="btn btn-primary uploadBtnUpload mdi mdi-upload">&nbsp;{{ __('Upload') }}</button>
                    <button type="button" class="btn btn-secondary mdi mdi-close" data-dismiss="modal">&nbsp;{{ __('Close') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
