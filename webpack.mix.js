let mix = require('laravel-mix');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

if (process.env.NODE_ENV == 'production') {
    mix.disableNotifications()
}
if (process.env.NODE_ENV != 'production') {
    // mix.browserSync('test.local');
    mix.version()
    mix.sourceMaps()
}

mix.webpackConfig({
    plugins: [
        new CleanWebpackPlugin({
            // dry: true,
            verbose: true,
            cleanOnceBeforeBuildPatterns: [
                '**/*',
                '!views','!views/**/*',
                '!lang', '!lang/**/*',
                '!js','!js/plugins', '!js/plugins/tinymce.omen.plugin.min.js',
            ],
            // hack to remove unwanted assets emmited from plugins
            cleanAfterEveryBuildPatterns: [
                'fonts/vendor'
            ],
        }),
    ]
});

mix.setPublicPath('resources');

mix.copy('node_modules/@mdi/font/fonts/', 'resources/fonts/');
mix.copy('src/images/favicon/dist/', 'resources/images/favicon/');
mix.copy('src/images/whitenoise-361x370.png', 'resources/images/');
mix.copy('src/images/loading.svg', 'resources/images/');
mix.copy('src/images/shadow.png', 'resources/images/');
mix.copy('src/images/griphandle.png', 'resources/images/');
mix.copy('src/images/fancytree-li.gif', 'resources/images/vendor/fancytree/');

mix.copy('node_modules/bootstrap-fileinput/img/loading.gif', 'resources/images/vendor/bootstrap-fileinput/');
mix.copy('node_modules/bootstrap-fileinput/img/loading-sm.gif', 'resources/images/vendor/bootstrap-fileinput/');

mix.copy('node_modules/mediaelement/build/mejs-controls.svg', 'resources/images/vendor/mediaelement/');
mix.copy('node_modules/mediaelement/build/mejs-controls.png', 'resources/images/vendor/mediaelement/');
mix.copy('node_modules/mediaelement/build/mediaelement-flash-video.swf', 'resources/images/vendor/mediaelement/');
mix.copy('node_modules/mediaelement/build/lang', 'resources/js/vendor/mediaelement/');

mix.sass('src/resources/sass/app.scss', 'css/__sass_omen.css');
mix.sass('src/resources/sass/omen/pdfCanvas.scss', 'css/omenPdf.css');
mix.less('src/resources/less/dependency.less', 'css/__less_omen.css');
mix.styles([
    'node_modules/mediaelement/build/mediaelementplayer.css',
    'resources/css/__less_omen.css',
    'resources/css/__sass_omen.css',
    'node_modules/simplebar/dist/simplebar.css',
    'node_modules/highlight.js/scss/vs2015.scss',
], 'resources/css/omen.css');


mix.coffee(['src/resources/coffee/app.coffee'], 'js/omen.js').extract([
    'bootstrap',
    'popper.js',
    'jquery',
    'lodash',
    'jquery.fancytree',
    'js-base64',
    'lazyload',
    'highlight.js',
    'mediaelement',
    'fuzzysearch',
    'fuzzyset.js',
    'split.js',

    // Simplebar depenencies
    'resize-observer-polyfill',
    'lodash.throttle',
    'lodash.memoize',
    'lodash.debounce',
    'can-use-dom',
    'core-js',

    // Simplebar
    'simplebar',

    'progressbar.js',
    'shifty',
    'bootstrap-fileinput',
    'jquery-contextmenu',
    'jquery-ui',
    'cropperjs',
    'bezier-easing',
    '@shopify/draggable',
    'wheelzoom'
]);

mix.scripts('node_modules/mediaelement/build/mediaelement-and-player.js', 'resources/js/vendor/mediaelement.min.js');

mix.scripts('node_modules/pdfjs-dist/build/pdf.js', 'resources/js/vendor/pdf.min.js');
mix.scripts('node_modules/pdfjs-dist/build/pdf.worker.js', 'resources/js/vendor/pdf.worker.min.js');
mix.scripts('node_modules/pdfjs-dist/web/pdf_viewer.js', 'resources/js/vendor/pdf.viewer.min.js');
mix.styles('node_modules/pdfjs-dist/web/pdf_viewer.css', 'resources/js/vendor/pdf.viewer.min.css');
