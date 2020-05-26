<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
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

        $response = null;

        try {
            $response = $next($request);
        } catch (Exception $e) {
            throw new OmenException('An unknow error happened in Omen', '00' . __LINE__, $e);
        }

        if (get_class($response) == "\Illuminate\Http\Response") {
            if (!empty($cspRuleLine)) {
                $response->header('Content-Security-Policy', $cspRuleLine);
            }

            $this->corsHandle($response);
        }


        return $response;
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
            throw new OmenException('Error while generating CSP resources rules', '7' . __LINE__, $e);
        }

        // security needed
        $cspOptionUrls['object-src']['none'] = "'none'";
        $cspOptionUrls['base-uri']['none'] = "'none'";

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

        return $cspRuleLine . 'report-uri ' . route('omenCspReport', [], false);
    }

    private function corsHandle(&$response)
    {
        if (config('omen.useXFrameOptions')) {
            $response->header('X-Frame-Options', 'sameorigin');
        } else $response->header('X-Frame-Options', 'deny');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('Referrer-Policy', 'same-origin');
        $response->header('Feature-Policy', 'fullscreen');
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
        try {
            if (\count(config('omen.locales')) == 1) {
                config(['omen.forceLocale' => \reset($locales)]);
                config(['omen.locale' => config('omen.forceLocale')]);
                App::setLocale(config('omen.forceLocale'));
                return;
            }
        } catch (Exception $e) {
            if (\strpos($e->getMessage(), 'count():') !== false) {
                throw new OmenException(__('"locales" in your configuration file must be an array'), 1, $e);
            }
            throw new OmenException(__('Something is wrong in your configuration file, please check locales and laravel log'), 1, $e);
        }
        if ($request->filled('locale')) {
            config(['omen.locale' => $request->get('locale')]);
            App::setLocale($request->get('locale'));
            $request->session()->put('omen.locale', $request->get('locale'));
            $request->session()->save();
        } elseif ($request->session()->has('omen.locale')) {
            App::setLocale($request->session()->get('omen.locale'));
            config(['omen.locale' => $request->session()->get('omen.locale')]);
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
            Session::put('omen.path', $request->get('path', '/'));
        } else {
            Session::put('omen.path', '/');
        }
    }
}
