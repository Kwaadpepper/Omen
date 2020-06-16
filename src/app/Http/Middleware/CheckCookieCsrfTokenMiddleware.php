<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\OmenHelper;

class CheckCookieCsrfTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Session::token() != Cookie::get('XSRF-TOKEN')) {
            return OmenHelper::abort(419, __('CSRF token mismatch.'));
        }
        return $next($request);
    }
}
