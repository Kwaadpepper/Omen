@php
$pathFolder = array_filter(explode('/', $path));
$pathSub = '';
@endphp
<nav class="" id="pathBreadcrumb" aria-label="breadcrumb">
    <a id="actionUpperDirectory" class="mdi mdi-rotate-90 mdi-subdirectory-arrow-left"><span class="sr-only">({{ __('omen::upper directory') }})</span></a>
    <div class="text-black-50">
        <div class="dropdown d-inline">
            <button class="btn btn-primary-outline p-0" id="fileSortButton" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mdi mdi-menu-up"></span>
                <span class="mdi mdi-sort-alphabetical-variant"></span>
                <span class="sr-only">{{ __('omen::sort files alphabeticaly') }}</span>
                <span class="sr-only">{{ __('omen::sort files button') }}</span>
            </button>
            <div class="dropdown-menu min-width-inherit" aria-labelledby="fileSortButton">
                <a class="dropdown-item" id="sortAlpha" href="">
                    <span class="mdi mdi-menu-up"></span>
                    <span class="mdi mdi-menu-down d-none"></span>
                    <span class="mdi mdi-sort-alphabetical-variant"></span>
                    <span class="sr-only">{{ __('omen::sort files alphabeticaly') }}</span>
                </a>
                <a class="dropdown-item" id="sortDate" href="">
                    <span class="mdi mdi-menu-up d-none"></span>
                    <span class="mdi mdi-menu-down"></span>
                    <span class="mdi mdi-calendar"></span>
                    <span class="sr-only">{{ __('omen::sort files by calendar') }}</span>
                </a>
                <a class="dropdown-item" id="sortSize" href="">
                    <span class="mdi mdi-menu-up d-none"></span>
                    <span class="mdi mdi-menu-down"></span>
                    <span class="mdi mdi-weight"></span>
                    <span class="sr-only">{{ __('omen::sort files by size') }}</span>
                </a>
                <a class="dropdown-item" id="sortType" href="">
                    <span class="mdi mdi-menu-up d-none"></span>
                    <span class="mdi mdi-menu-down"></span>
                    <span class="mdi mdi-file-question-outline"></span>
                    <span class="sr-only">{{ __('omen::sort files by type') }}</span>
                </a>
            </div>
        </div>
        <span class="mdi mdi-folder" id="folderCounter">3<span class="sr-only">({{ __('omen::number of folders') }})</span></span>
        <span class="mdi mdi-file" id="fileCounter">8<span class="sr-only">({{ __('omen::number of files') }})</span></span>
    </div>
    <ol class="breadcrumb flex-nowrap" id="pathBreadcrumbList">
        <li class="breadcrumb-item"><a href="{{ route('omenInterface', $query) }}" class="mdi mdi-home"><span class="sr-only">({{ __('omen::root directory') }})</span></a></li>
        @foreach($pathFolder as $folder)

        @php $pathSub .= "/$folder" @endphp

        @if ($loop->last)
        <li class="breadcrumb-item active" aria-current="page">{{ Str::ucfirst($folder) }}</li>
        @else
        <li class="breadcrumb-item"><a href="{{ route('omenInterface', array_merge($query, ['path' => $pathSub])) }}">{{ Str::ucfirst($folder) }}</a></li>
        @endif

        @endforeach
    </ol>
</nav>

@push('scripts')
<script nonce="{{ config('omen.cspToken') }}">
    $(document).ready(function() {
        document.getElementById('pathBreadcrumbList').scrollLeft = 9999999;
    })

</script>
@endpush
