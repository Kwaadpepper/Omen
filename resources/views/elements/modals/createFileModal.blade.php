<div id="newFileModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="newFileForm">
                <div class="modal-header">
                    <h5 class="ml-4">{{ __('omen::Create a text file') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-toggle="tooltip" title="{{ __('omen::Close') }}">
                        <span class="sr-only">({{ __('omen::Close') }})</span>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newFileNameInput">{{ __('omen::File name')}}</label>
                        <input type="text" class="form-control" id="newFileNameInput" name="filename" required>
                    </div>
                    <div class="form-group">
                        <label for="newFileTextInput">{{ __('omen::File text')}}</label>
                        <div class="container-fluid">
                            <code contenteditable="false">
                                <pre class="pb-4" id="newFileTextInput" role="input" name="filetext" contenteditable></pre>
                            </code>
                        </div>
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
