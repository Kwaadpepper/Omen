@php
$urlPrefix = config('omen.urlPrefix');
$assetPath = config('omen.assetPath');

@endphp
<!DOCTYPE html>
<html lang="{{ config('omen.locale') }}">
<head>
    {{-- TITLE --}}
    <title>{{ config('omen.title') }}</title>

    {{-- META --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="{{ $apiCSRFToken['name'] }}" content="{{ $apiCSRFToken['key'] }}">
    <link rel="dns-prefetch" href="//content.googleapis.com">
    <link rel="dns-prefetch" href="//apis.google.com">
    <link rel="dns-prefetch" href="//clients6.google.com">
    <link rel="dns-prefetch" href="//www.gstatic.com">
    <link rel="preload" href="{{ asset(sprintf('%s/js/manifest.js', $assetPath)) }}" as="script">
    <link rel="preload" href="{{ asset(sprintf('%s/js/vendor.js', $assetPath)) }}" as="script">
    <link rel="preload" href="{{ asset(sprintf('%s/js/omen.js', $assetPath)) }}" as="script">
    <link rel="preload" href="{{ asset(sprintf('%s/js/vendor/mediaelement.min.js', $assetPath)) }}" as="script">
    <link rel="preload" href="{{ asset(sprintf('%s/css/omen.css', $assetPath)) }}" as="style">

    {{-- FAVICON : https://realfavicongenerator.net/ --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset(sprintf('%s/images/favicon/apple-touch-icon.png', $assetPath)) }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset(sprintf('%s/images/favicon/favicon-32x32.png', $assetPath)) }}">
    <link rel="icon" type="image/png" sizes="194x194" href="{{ asset(sprintf('%s/images/favicon/favicon-194x194.png', $assetPath)) }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset(sprintf('%s/images/favicon/android-chrome-192x192.png', $assetPath)) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset(sprintf('%s/images/favicon/favicon-16x16.png', $assetPath)) }}">
    <link rel="manifest" href="{{ asset(sprintf('%s/images/favicon/site.webmanifest', $assetPath)) }}">
    <link rel="mask-icon" href="{{ asset(sprintf('%s/images/favicon/safari-pinned-tab.svg', $assetPath)) }}" color="#1b669a">
    <meta name="apple-mobile-web-app-title" content="{{ config('omen.title') }}">
    <meta name="application-name" content="{{ config('omen.title') }}">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="msapplication-TileImage" content="{{ asset(sprintf('%s/images/favicon/mstile-144x144.png', $assetPath)) }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="shortcut icon" href="{{ asset(sprintf('%s/images/favicon/favicon.ico', $assetPath)) }}" type="application/ico">

    {{-- LINK --}}
    <link nonce="{{ config('omen.cspToken') }}" rel="stylesheet" href="{{ asset(sprintf('%s/css/omen.css', $assetPath)) }}">
    {{-- SCRIPT --}}
    <script nonce="{{ config('omen.cspToken') }}" src="{{ asset(sprintf('%s/js/manifest.js', $assetPath)) }}"></script>
    <script nonce="{{ config('omen.cspToken') }}" src="{{ asset(sprintf('%s/js/vendor/mediaelement.min.js', $assetPath)) }}"></script>
    <script nonce="{{ config('omen.cspToken') }}" src="{{ asset(sprintf('%s/js/vendor.js', $assetPath)) }}"></script>
    <script nonce="{{ config('omen.cspToken') }}" src="{{ asset(sprintf('%s/js/omen.js', $assetPath)) }}"></script>
</head>
<body>
    @include('omen::elements.loadingSplash')
    <div class="container-fluid p-0 vh-100 d-flex flex-column">
        @include('omen::elements.navbar')
        <div class="row m-0 flex-grow-1" id="omenView">
            {{-- Left Panel --}}
            <div class="col-md-4 col-12 p-0 d-flex flex-column" id="leftPanel">
                @include('omen::elements.leftPanel')
            </div>
            <div class="col-md-8 col-12 p-0 d-flex flex-column flex-grow-1" id="viewInodes">
                <div class="">
                    @include('omen::elements.breadcrumb', compact('query', 'path'))
                </div>
                <div class="flex-grow-1 d-flex flex-wrap justify-content-start align-items-baseline">
                    <div class="d-block viewIcon" id="inodesContainer">
                        @include('omen::elements.viewListTopBar')
                        @include('omen::elements.inodesView.view', compact('inodes', 'path'))
                    </div>
                    @include('omen::elements.wysiwygButton')
                </div>
            </div>
        </div>
    </div>
    @include('omen::elements.alert.success')
    @include('omen::elements.alert.info')
    @include('omen::elements.alert.warning')
    @include('omen::elements.alert.danger')
    @include('omen::elements.modals.uploadFileModal')
    @include('omen::elements.modals.aboutModal')
    @include('omen::elements.modals.renameModal')
    @include('omen::elements.modals.createFileModal')
    @include('omen::elements.modals.createDirectoryModal')
    @include('omen::elements.modals.imageEditorModal')
    @include('omen::elements.modals.editFileModal')
    @include('omen::elements.modals.viewer.imageModal')
    @include('omen::elements.modals.viewer.textModal')
    @include('omen::elements.modals.viewer.pdfModal')
    @include('omen::elements.modals.viewer.documentModal')
    @include('omen::elements.modals.viewer.videoModal')
    @include('omen::elements.modals.viewer.audioModal')
    @include('omen::elements.operationsBar')
    @include('omen::elements.lostConnectionBanner')
    @include('omen::tools.translations')
    @include('omen::tools.config', compact('inodes'))
    @stack('css')
    @stack('scripts')
</body>
</html>
