<div id="editFileModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editFileForm">
                <div class="modal-header">
                    <h5 class="ml-4">{{ __('omen::File name') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-toggle="tooltip" title="{{ __('omen::Close') }}">
                        <span class="sr-only">({{ __('omen::Close') }})</span>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="container-fluid">
                            <code contenteditable="false">
                                <pre class="pb-4" id="editFileTextInput" role="input" name="filetext" contenteditable></pre>
                            </code>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
