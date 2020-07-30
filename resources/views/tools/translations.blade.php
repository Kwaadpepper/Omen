@push('scripts')
<div id="frontTransations" class="d-none">
    <script nonce="{{ config('omen.cspToken') }}">
        __omenTranslations = {
            @php
            $i = 0;
            $translations = cache('translations');
            foreach($translations as $string => $trans) {
                echo sprintf('"%s": "%s"%s', str_replace('omen::', '', $string), $trans, ',');
            }
            @endphp
        };

    </script>
</div>
@endpush
