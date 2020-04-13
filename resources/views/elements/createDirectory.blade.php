<div id="newDirectoryModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="newDirectoryForm">
                <div class="modal-header">
                    <h5 class="ml-4">{{ __('Create a directory') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span class="sr-only">({{ __('omen::close create directory modal') }})</span>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newDirectoryNameInput">{{ __('Directory name')}}</label>
                        <input type="text" class="form-control" id="newDirectoryNameInput" name="directoryname" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
