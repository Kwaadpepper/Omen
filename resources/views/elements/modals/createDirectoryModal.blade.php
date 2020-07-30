<div id="newDirectoryModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="newDirectoryForm">
                <div class="modal-header">
                    <h5 class="ml-4">{{ __('omen::Create a directory') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-toggle="tooltip" title="{{ __('omen::Close') }}">
                        <span class="sr-only">({{ __('omen::Close') }})</span>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newDirectoryNameInput">{{ __('omen::Directory name')}}</label>
                        <input type="text" class="form-control" id="newDirectoryNameInput" name="directoryname" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('omen::Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('omen::Create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
