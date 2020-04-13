@push('scripts')
<div id="frontTransations" class="d-none">
    <script nonce="{{ config('omen.cspToken') }}">
        __omenTransalations = {
            "Name changed": "{{ __('omen::Name changed') }}"
            , "File was renamed in ${filename}": "{{ __('omen::File was renamed in ${filename}') }}"
            , "Action failure": "{{ __('omen::Action failure') }}"
            , "Could not rename file ${filename}, server said no": "{{ __('omen::Could not rename file ${filename}, server said no') }}"
            , "File download error": "{{ __('omen::File download error') }}"
            , "Server could not get ${filename}": "{{ __('omen::Server could not get ${filename}') }}"
            , "File delete error": "{{ __('omen::File delete error') }}"
            , "Server error on delete ${filename}": "{{ __('omen::Server error on delete ${filename}') }}"
            , "File deletion": "{{ __('omen::File deletion') }}"
            , "File is removed": "{{ __('omen::File is removed') }}"
            , "Error": "{{ __('omen::Error') }}"
            , "Could not retrieve text file": "{{ __('omen::Could not retrieve text file') }}"
            , "Could not retrieve image file": "{{ __('omen::Could not retrieve image file') }}"
            , "Wrong input": "{{ __('omen::Wrong input') }}"
            , "the file name shall be at least 3 characters": "{{ __('omen::the file name shall be at least 3 characters') }}"
            , "File check error": "{{ __('omen::File check error') }}"
            , "Directory check error": "{{ __('omen::Directory check error') }}"
            , "Server could not say if ${inodename} exists": "{{ __('omen::Server could not say if ${inodename} exists') }}"
            , "This file name already exists !": "{{ __('omen::This file name already exists !') }}"
            , "This file directory already exists !": "{{ __('omen::This directory name already exists !') }}"
            , "Please choose another name than ${inodename}": "{{ __('omen::Please choose another name than ${inodename}') }}"
            , "File created": "{{ __('omen::File created') }}"
            , "Directory created": "{{ __('omen::Directory created') }}"
            , "${inodename} has been created": "{{ __('omen::${inodename} has been created') }}"
            , "Could not create ${inodename}, server said no": "{{ __('omen::Could not create ${inodename}, server said no') }}"
            , "B": "{{ __('omen::B') }}"
            , "KB": "{{ __('omen::KB') }}"
            , "MB": "{{ __('omen::MB') }}"
            , "GB": "{{ __('omen::GB') }}"
            , "TB": "{{ __('omen::TB') }}"
            , "PB": "{{ __('omen::PB') }}"
            , "EB": "{{ __('omen::EB') }}"
            , "ZB": "{{ __('omen::ZB') }}"
            , "YB": "{{ __('omen::YB') }}"
        }

    </script>
</div>
@endpush
