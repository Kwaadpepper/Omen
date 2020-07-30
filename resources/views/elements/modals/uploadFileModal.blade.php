<div id="uploadModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form id="uploadForm" method="post" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="ml-4">{{ __('omen::Upload files') }}</h5>
                    <div>
                        <button type="button" class="btn btn-secondary pauseBtnUpload mdi mdi-pause">&nbsp;{{ __('omen::Pause') }}</button>
                        <button type="button" class="btn btn-secondary resumeBtnUpload mdi mdi-play">&nbsp;{{ __('omen::Resume') }}</button>
                        <button type="button" class="btn btn-secondary cancelBtnUpload mdi mdi-cancel">&nbsp;{{ __('omen::Cancel') }}</button>
                        <button type="button" class="btn btn-secondary clearBtnUpload mdi mdi-delete">&nbsp;{{ __('omen::Clear') }}</button>
                        <button type="button" class="btn btn-primary browseBtnUpload mdi mdi-folder-open">&nbsp;{{ __('omen::Choose') }}...</button>
                        <button type="submit" class="btn btn-primary uploadBtnUpload mdi mdi-upload">&nbsp;{{ __('omen::Upload') }}</button>
                        <button type="button" class="btn btn-secondary mdi mdi-close" data-dismiss="modal">&nbsp;{{ __('omen::Close') }}</button> </div>
                </div>
                <div class="modal-body">
                    <input id="uploadInput" type="file" name="files[]" multiple="multiple">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary pauseBtnUpload mdi mdi-pause">&nbsp;{{ __('omen::Pause') }}</button>
                    <button type="button" class="btn btn-secondary resumeBtnUpload mdi mdi-play">&nbsp;{{ __('omen::Resume') }}</button>
                    <button type="button" class="btn btn-secondary cancelBtnUpload mdi mdi-cancel">&nbsp;{{ __('omen::Cancel') }}</button>
                    <button type="button" class="btn btn-secondary clearBtnUpload mdi mdi-delete">&nbsp;{{ __('omen::Clear') }}</button>
                    <button type="button" class="btn btn-primary browseBtnUpload mdi mdi-folder-open">&nbsp;{{ __('omen::Choose') }}...</button>
                    <button type="submit" class="btn btn-primary uploadBtnUpload mdi mdi-upload">&nbsp;{{ __('omen::Upload') }}</button>
                    <button type="button" class="btn btn-secondary mdi mdi-close" data-dismiss="modal">&nbsp;{{ __('omen::Close') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
