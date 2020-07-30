<div class="modal fade" id="renameModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="renameForm" action="/rename">
                <div class="modal-header">
                    <h5 class="ml-4">{{ __('omen::Rename') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-toggle="tooltip" title="{{ __('omen::Close') }}">
                        <span class="sr-only">({{ __('omen::Close') }})</span>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="renameInput">{{ __('omen::Name')}}</label>
                        <input type="text" class="form-control" id="renameInput" name="filename">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary d-none edit" data-dismiss="modal">{{ __('omen::Edit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('omen::Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('omen::Rename') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
