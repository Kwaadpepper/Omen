<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Kwaadpepper\Omen\Exceptions\OmenDebugException;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\OmenHelper;

class OmenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->localeHandle($request);

        $this->interfaceParamsHandle($request);
        $cspRuleLine = $this->csfpHandle();

        $this->checkExtensionsHandle();

        $response = $next($request);

        if (get_class($response) == "Illuminate\Http\Response") {
            if (!empty($cspRuleLine)) {
                $response->header('Content-Security-Policy', $cspRuleLine);
            }

            $this->corsHandle($response);
        }


        return $response;
    }

    private function checkExtensionsHandle()
    {
        $imageDriver = config('omen.fileOperationImageDriver');
        if ($imageDriver != 'gd' and $imageDriver != 'imagick') {
            config(['omen.imageLib' => false]);
            report(new OmenDebugException(sprintf('Asked image lib %s but only "gd" or "imagick" are supported', $imageDriver)));
            return;
        }
        if ($imageDriver == 'gd' and \extension_loaded('gd')) {
            config(['omen.imageLib' => true]);
        } else if ($imageDriver == 'imagick' and \extension_loaded('imagick')) {
            config(['omen.imageLib' => true]);
        } else {
            config(['omen.imageLib' => false]);
            report(new OmenDebugException(sprintf('Asked image lib %s, but it is not enabled', $imageDriver)));
        }
    }

    private function csfpHandle()
    {
        $cspRuleLine = '';
        $cspOptionUrls = [];

        if (\is_iterable(config('omen.csp'))) {
            foreach (config('omen.csp') as $policy => $sources) {
                if (\is_iterable($sources)) {
                    $cspOptionUrls[$policy] = $cspOptionUrls[$policy] ?? [];
                    foreach ($sources as $source) {
                        $cspOptionUrls[$policy][$source] = $source;
                    }
                }
            }
        }

        // add nonce rules for assets

        try {
            $randomString = Str::random();
            config(['omen.cspToken' => $randomString]);
            $cspOptionUrls['default-src'][] = \sprintf("'nonce-%s'", $randomString);
            $cspOptionUrls['script-src'][] = \sprintf("'nonce-%s'", $randomString);
            $cspOptionUrls['style-src'][] = \sprintf("'nonce-%s'", $randomString);
        } catch (Exception $e) {
            throw new OmenException('Error while generating CSP resources rules', $e);
        }

        // security needed
        foreach (['object-src', 'base-uri'] as $directiveName) {
            if (
                !\array_key_exists($directiveName, $cspOptionUrls) or
                !\is_array($cspOptionUrls[$directiveName]) or
                !\count($cspOptionUrls[$directiveName])
            ) {
                $cspOptionUrls[$directiveName] = ['none' => "'none'"];
            }
        }

        // If documentEmbedViewer Url is not valid
        if (!\filter_var(config('omen.documentEmbedViewer'), \FILTER_VALIDATE_URL)) {
            // Then disable the feature
            config(['omen.documentEmbedViewer' => false]);
        } else {
            // Allow document viewer Domain
            $source = \sprintf(
                '%s://%s',
                \parse_url(config('omen.documentEmbedViewer'), \PHP_URL_SCHEME),
                \parse_url(config('omen.documentEmbedViewer'), \PHP_URL_HOST)
            );
            $cspOptionUrls['frame-src'][$source] = $source;
        }

        // Generate csp rules
        foreach ($cspOptionUrls as $policy => $sources) {
            $cspRuleLine .= !empty($cspOptionUrls[$policy]) ? \sprintf('%s %s; ', $policy, \implode(' ', $cspOptionUrls[$policy])) : '';
        }

        return $cspRuleLine . 'report-uri ' . route('OmenReport.omenCspReport', [], false);
    }

    private function corsHandle(&$response)
    {
        if (config('omen.useXFrameOptions')) {
            $response->header('X-Frame-Options', 'sameorigin');
        } else $response->header('X-Frame-Options', 'deny');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('Referrer-Policy', 'no-referrer');
        $response->header('Feature-Policy', \implode(
            ' ',
            [
                "autoplay 'self';",
                "fullscreen 'self';",
                "layout-animations 'self';",
                "legacy-image-formats 'self';",
                "midi 'self';",
                "navigation-override 'self';",
                "oversized-images 'self';",
                "picture-in-picture 'self';",
                "sync-xhr 'self';",
                "accelerometer 'none';",
                "ambient-light-sensor 'none';",
                "battery 'none';",
                "camera 'none';",
                "display-capture 'none';",
                "document-domain 'none';",
                "encrypted-media 'none';",
                "execution-while-not-rendered 'none';",
                "execution-while-out-of-viewport 'none';",
                "geolocation 'none';",
                "gyroscope 'none';",
                "magnetometer 'none';",
                "microphone 'none';",
                "payment 'none';",
                "publickey-credentials-get 'none';",
                "usb 'none';",
                "vr 'none';",
                "wake-lock 'none';",
                "xr-spatial-tracking 'none';"
            ]
        ));
    }

    /**
     * Set app Locale with environement vars
     * @param \Illuminate\Http\Request $request 
     * @return void 
     * @throws OmenException if wrong configuration in omen.php
     */
    private function localeHandle($request)
    {
        // need to update config('omen.locale') (config is passed to front) and App::setLocale here

        if (config('omen.forceLocale')) {
            config(['omen.locale' => config('omen.forceLocale')]);
            App::setLocale(config('omen.forceLocale'));
            return;
        }
        /**
         * Check if the user has set only one locale then force
         * the usage of this one only
         */
        $locales = null;
        try {
            $locales = config('omen.locales');
            if (\count($locales) == 1) {
                config(['omen.forceLocale' => \reset($locales)]);
                config(['omen.locale' => config('omen.forceLocale')]);
                App::setLocale(config('omen.forceLocale'));
                return;
            }
        } catch (Exception $e) {
            if (\strpos($e->getMessage(), 'count():') !== false) {
                throw new OmenException(__('"locales" in your configuration file must be an array'), $e);
            }
            throw new OmenException(__('Something is wrong in your configuration file, please check locales and laravel log'), $e);
        }
        if ($request->filled('locale')) {
            $rL = $request->get('locale');
            $rL = \array_search($rL, $locales) !== false ? $rL : 'en';
            config(['omen.locale' => $rL]);
            App::setLocale($rL);
            $request->session()->put('omen.locale', $rL);
            $request->session()->save();
        } elseif ($request->session()->has('omen.locale')) {
            App::setLocale($request->session()->get('omen.locale'));
            config(['omen.locale' => $request->session()->get('omen.locale')]);
        } else {
            $pL = $request->getPreferredLanguage() ?? 'en';
            $pL = \strpos($pL, '_') !== false ? \explode('_', $pL)[0] ?? 'en' : 'en';
            $pL = \array_search($pL, $locales) !== false ? $pL : 'en';
            App::setLocale($pL);
            config(['omen.locale' => $pL]);
        }
    }

    /**
     * Parse and set default Interface Parameters
     * 
     ** PATH  type: query  name: path  value: string ('' || '/') or '/folder' or '/folder/subfolder'
     * 
     * @param  \Illuminate\Http\Request $request 
     * @return void 
     */
    private function interfaceParamsHandle($request)
    {
        // PATH
        if ($request->filled('path') and $request->get('path') != Session('omen.path')) {
            $request->session()->put('omen.path', $request->get('path', '/'));
        } else {
            $request->session()->put('omen.path', '/');
        }

        // TYPE
        if ($request->filled('type') or $request->filled('editor')) {
            $type = $request->get('type', $request->session()->get('omen.type'));
            $cfg = \array_flip(config('omen.showFileTypes'));
            switch ($type) {
                case 'media':
                    unset($cfg['file']);
                    unset($cfg['archive']);
                    unset($cfg['image']);
                    break;
                case 'image':
                    unset($cfg['file']);
                    unset($cfg['video']);
                    unset($cfg['audio']);
                    unset($cfg['archive']);
                    break;
            }
            $cfg = array_values(\array_flip($cfg));
            $request->session()->put('omen.type', $type);
            $request->session()->put('omen.showFileTypes', $cfg);
            config(['omen.showFileTypes' => $cfg]);
        }
        $request->session()->save();
    }
}
