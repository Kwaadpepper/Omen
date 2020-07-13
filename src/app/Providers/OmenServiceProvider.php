<?php

namespace Kwaadpepper\Omen\Providers;

use \Illuminate\Support\ServiceProvider;
use \Illuminate\Routing\Router;
use Jenssegers\Date\DateServiceProvider;
use \Blade as _Blade;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Http\Middleware\OmenErrorHandlerMiddleware;
use Kwaadpepper\Omen\Http\Middleware\OmenThrottleMiddleware;

class OmenServiceProvider extends ServiceProvider
{
    protected $commands = [
        'Kwaadpepper\Omen\Console\Commands\OmenLinkStorage'
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->app->register(DateServiceProvider::class);
        $this->configLoadIfNeeded();

        // Add package routes.
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../../../resources/lang/');
        $this->loadViewsFrom(__DIR__ . '/../../../resources/views', 'omen');

        $router->aliasMiddleware('omenthrottle', OmenThrottleMiddleware::class);
        $router->aliasMiddleware('omenerrorhandler', OmenErrorHandlerMiddleware::class);
        $router->middlewareGroup(
            'OmenMiddleware',
            array(
                \App\Http\Middleware\TrimStrings::class,
                \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
                \App\Http\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \App\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Kwaadpepper\Omen\Http\Middleware\OmenMiddleware::class
            )
        );

        $omenPublicPath = config('omen.assetPath');

        $this->publishes([__DIR__ . '/../../config/omenFilemanager.php' => config_path('omenFilemanager.php')], 'config');

        $this->publishes([
            __DIR__ . '/../../../resources/js' => public_path($omenPublicPath . '/js'),
            __DIR__ . '/../../../resources/css' => public_path($omenPublicPath . '/css'),
            __DIR__ . '/../../../resources/img' => public_path($omenPublicPath . '/img')
        ], 'assets');

        $this->publishes([
            __DIR__ . '/../../../resources/views' => resource_path('views/vendor/omen/')
        ], 'views');

        $this->publishes([
            __DIR__ . '/../../../resources/lang/' => resource_path('lang/vendor/omen/')
        ], 'lang');


        // Blade::directive(
        //     'ckeditorPlugin',
        //     function () use ($FMPUBPATH) {
        //         return $FMPUBPATH . '/';
        //     }
        // );

        Blade::directive(
            'omenPath',
            function ($query) {
                \parse_str($query, $array);
                $array = !\count($array) ? ['path' => '/', 'type' => 'all'] : $array;
                if (!\array_key_exists('type', $array)) {
                    $array['type'] = 'all';
                }
                return route('omenInterface', $array);
            }
        );

        Blade::directive(
            'tinymcePlugin',
            function () {
                return \sprintf(
                    '<script src="%s"></script>',
                    route('httpFileSend.omenAsset', ['fileUri' => 'js/plugins/tinymce.omen.plugin.min.js'])
                );
            }
        );
        Blade::directive(
            'tinymcePluginPath',
            function () {
                return route('httpFileSend.omenAsset', ['fileUri' => 'js/plugins/tinymce.omen.plugin.min.js']);
            }
        );

        // Blade::directive(
        //     'filemanager_get_key',
        //     function () {
        //         $o = isset(config('rfm.access_keys')[0]) ? config('rfm.access_keys')[0] : ''; //phpcs:ignore
        //         return urlencode($o);
        //     }
        // );

        // Blade::directive(
        //     'filemanager_get_resource',
        //     function ($file) use ($FMVENDOR) {
        //         $r = parse_url(route('FM' . $file), PHP_URL_PATH);
        //         if ($r) {
        //             return $r;
        //         }
        //         if (isset($FMVENDOR[$file])) {
        //             return $FMVENDOR[$file];
        //         }
        //         if (config('app.debug')) {
        //             throw new \Exception('unkown file ' . $file . ' in Reponsive File Manager'); //phpcs:ignore
        //         }
        //     }
        // );

        // Blade::directive(
        //     'filemanager_get_config',
        //     function ($expression) {
        //         return config($expression);
        //     }
        // );
    }

    /**
     * Overwrite any vendor / package configuration.
     *
     * This service provider is intended to provide a convenient location for you
     * to overwrite any "vendor" or package configuration that you may want to
     * modify before the application handles the incoming request / command.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }


    private function configLoadIfNeeded()
    {
        if (!config('omen.omenIsLoaded')) {
            $config = require __DIR__ . '/../../config/omen.php';
            config(['omen' => $config]);
        }

        // Load package info
        $json = \json_decode(\file_get_contents(__DIR__ . '/../../../composer.json'));
        config(['omen.package.name' => $json->name]);
        config(['omen.package.description' => $json->description]);
        config(['omen.package.version' => $json->version]);
        config(['omen.package.license' => $json->license]);
        config(['omen.package.authors' => $json->authors]);
    }
}
