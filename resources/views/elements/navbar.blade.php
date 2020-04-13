<nav class="navbar navbar-expand-md" id="topNavBar">
    <button class="btn btn-outline-dark mdi mdi-dots-vertical" id="leftPanelToggler" aria-label="{{ __('omen::Left panel') }}">
    </button>
    <div class="w-100" id="topNavBarContent">
        <div class="btn-toolbar mr-auto col-12 pr-0 pl-0" role="toolbar" aria-label="{{ __('omen::File toolbar') }}">
            {{-- Nav Bar Toggler --}}
            <button class="collapsed btn btn-outline-dark mdi mdi-menu" id="navBarToggler" type="button" data-toggle="collapse" data-target="#filterButtonGroup" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            </button>
            <div class="btn-group" role="group" id="actionButtonGroup" aria-label="{{ __('omen::File operations') }}">
                <button class="btn btn-outline-dark mdi mdi-upload" id="actionUpload" type="button" aria-label="{{ __('omen::Upload Files') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Upload Files') }}">
                    <span class="sr-only">({{ __('omen::upload') }})</span>
                </button>
                <button class="btn btn-outline-dark mdi mdi-file-plus-outline" id="actionNewFile" type="button" aria-label="{{ __('omen::New file') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::New file') }}"> <span class="fa fa-lg fa-plus"></span>
                    <span class="sr-only">({{ __('omen::new file') }})</span>
                </button>
                <button class="btn btn-outline-dark mdi mdi-folder-plus-outline" id="actionNewDirectory" type="button" aria-label="{{ __('omen::New folder') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::New folder') }}">
                    <span class="sr-only">({{ __('omen::new folder') }})</span>
                </button>
            </div>
            <div class="btn-group" role="group" id="viewButtonGroup" aria-label="{{ __('omen::View Layout') }}">
                <button class="btn btn-outline-dark mdi mdi-view-grid active" id="viewIcon" type="button" aria-label="{{ __('omen::Icon view') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Icon view') }}">
                    <span class="sr-only">({{ __('omen::box view') }})</span>
                </button>
                <button class="btn btn-outline-dark mdi mdi-view-sequential" id="viewList" type="button" aria-label="{{ __('omen::List view') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::List view') }}">
                    <span class="sr-only">({{ __('omen::list view') }})</span>
                </button>
            </div>
            <div class="input-group collapse navbar-collapse" id="filterButtonGroup" role="group" aria-label="{{ __('omen::Files filter') }}">
                <div class="input-group-prepend">
                    <span class="input-group-text">{{ __('omen::Filters') }}&nbsp;:</span>
                </div>
                <div class="input-group-append btn-toolbar btn-group" role="toolbar" aria-label="{{ __('omen::Show only certain file type') }}">
                    <button class="btn btn-outline-dark mdi  mdi-file-outline" id="filterFiles" aria-label="{ __('omen::Show only files') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Show only files') }}">
                        <span class="sr-only">({{ __('omen::Show only files') }})</span>
                    </button>
                    <button class="btn btn-outline-dark mdi mdi-archive" id="filterArchives" aria-label="{{ __('omen::Show only archives files') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Show only archive files') }}">
                        <span class="sr-only">({{ __('omen::Show only archives files') }})</span>
                    </button>
                    <button class="btn btn-outline-dark mdi mdi-image" id="filterImages" aria-label="{{ __('omen::Show only images files') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Show only image files') }}">
                        <span class="sr-only">({{ __('omen::Show only images files') }})</span>
                    </button>
                    <button class="btn btn-outline-dark mdi mdi-video" id="filterVideo" aria-label="{{ __('omen::Show only video files') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Show only video files') }}">
                        <span class="sr-only">({{ __('omen::Show only video files') }})</span>
                    </button>
                    <button class="btn btn-outline-dark mdi mdi-music" id="filterAudio" aria-label="{{ __('omen::Show only audio files') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Show only audio files') }}">
                        <span class="sr-only">({{ __('omen::Show only audio files') }})</span>
                    </button>
                </div>
                <div class="input-group-append btn-toolbar btn-group" role="toolbar" aria-label="{{ __('omen::Search any files') }}">
                    <input class="form-control" id="filterInputText" type="search" placeholder="{{ __('omen::text filter..') }}" aria-label="{{ __('omen::text filter..') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Search using file name') }}">
                    <button class="btn btn-outline-dark mdi  mdi-magnify" id="filterSearch" type="submit" aria-label="{{ __('omen::Search using text filter') }}" data-toggle="tooltip" data-placement="bottom" title="{{ __('omen::Search everywhere for a file name') }}">
                        <span class="sr-only">({{ __('omen::Search file') }})</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>
