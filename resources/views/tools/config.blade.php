@php
$data = json_encode([
config("omen"),
$inodes
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

@push('scripts')
<script nonce="{{ config('omen.cspToken') }}">
    window.__omen_data = '{!! base64_encode($data) !!}';

</script>
@endpush
