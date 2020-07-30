<figure class="@yield('figureType' . $id) @yield('extensionClass' . $id) draggable-source" data-path="@yield('path'.$id)">
    @if($id != 'root')
    <div class="figHoverZone @yield('figureType' . $id)"></div>
    <div class="figAction bg-dark @yield('figureType' . $id)">
        @if($inodeType != 'directory')
        <button class="actionDownload download btn btn-dark mdi mdi-arrow-down-bold-circle-outline" data-toggle="tooltip" data-placement="top" title="{{ __('omen::Download') }}">
            <span class="sr-only">({{ __('omen::Download') }})</span></button>
        @if($view)
        <button class="actionView preview btn btn-dark mdi mdi-eye-circle-outline" data-toggle="tooltip" data-placement="top" title="{{ __('omen::View') }}">
            <span class="sr-only">({{ __('omen::View') }})</span></button>
        @endif
        @endif
        <button class="actionRename rename btn btn-dark mdi mdi-pencil-circle" data-toggle="tooltip" data-placement="top" title="{{ __('omen::Rename') }}">
            <span class="sr-only">({{ __('omen::Rename') }})</span></button>
        <button class="actionDelete delete btn btn-dark mdi mdi-delete-circle" data-toggle="tooltip" data-placement="top" title="{{ __('omen::Delete') }}">
            <span class="sr-only">({{ __('omen::Delete') }})</span></button>
    </div>
    <div class="figExt @yield('extensionClass' . $id)">
        <span>@yield('extension'. $id)</span>
    </div>
    @endif
    <div class="figBackground bg-dark @yield('figureType' . $id)"></div>
    @if($id != 'root')
    <label class="figCheck customCheckbox" data-toggle="tooltip" title="{{ __('omen::Select element')}}">
        <input type="checkbox" name="@yield('filepath'. $id)" id="@yield('id'. $id)">
        <span class="checkmark"></span>
    </label>
    @endif
    @if($id != 'root')
    <div class="figIcon mdi @yield('classType'. $id)"></div>
    @else
    <div class="figIcon mdi mdi-arrow-left"></div>
    @endif
    <img class="figThumb" src="@yield('thumbnail'. $id)">
    <figcaption>@yield('name'. $id)</figcaption>
    <div class="figDate">@yield('date'. $id)</div>
    <div class="figSize">@yield('size'. $id)</div>
    @if ($__env->yieldContent('visibility'. $id) == 'public')
    <div class="figVisibility mdi mdi-earth" data-visibility="{{ __('public') }}"></div>
    @else
    <div class="figVisibility mdi mdi-eye-off-outline" data-visibility="{{ __('private') }}"></div>
    @endif
</figure>
