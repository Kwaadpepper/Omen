<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Session\TokenMismatchException;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\CSRF;
use Kwaadpepper\Omen\OmenHelper;

class OmenApiCSRFMiddleware
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
        $response = null;
        try {
            // Check Laravel CSRF token first
            if (
                (!$this->checkOmenCSRFToken($request) and $request->route()->getName() != 'OmenApi.omenUpload') and
                !$this->checkLaravelCSRFToken($request)
            ) {
                return OmenHelper::abort(419, __('CSRF token mismatch.'));
            }

            $response = $next($request);

            if (!$this->isUploadRoute($request)) {
                $this->addCSRFTokenToResponse($response);
            }
        } catch (Exception $e) {
            throw new OmenException('An unknow error happened in Omen', $e);
        }

        return $response;
    }

    private function isUploadRoute($request)
    {
        return  $request->route()->getActionName() == 'OmenApi.omenUpload';
    }

    private function addCSRFTokenToResponse(&$response)
    {
        $response->header(CSRF::getHeaderName(), CSRF::generate());
    }

    private function checkOmenCSRFToken($request)
    {
        return CSRF::check($request);
    }

    private function checkLaravelCSRFToken($request)
    {
        $sessionToken = session()->token();
        $header = $request->header('X-CSRF-TOKEN') ?? $request->_token;
        return $sessionToken == $header;
    }
}
