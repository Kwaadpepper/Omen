<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Session\TokenMismatchException;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\CSRF;

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
        if (!$this->isUploadRoute($request)) {
        }
        $response = null;
        try {
            // Check Laravel CSRF token first
            if (
                (!$this->checkLaravelCSRFToken($request) and $request->route()->getName() != "omenUpload") or
                !$this->checkOmenCSRFToken($request)
            ) {
                return \response('', 419);
            }

            $response = $next($request);

            if ($request->route()->getName() != "omenUpload") {
                $this->addCSRFTokenToResponse($response);
            }
        } catch (Exception $e) {
            throw new OmenException('An unknow error happened in Omen', '05' . __LINE__, $e);
        }

        return $response;
    }

    private function isUploadRoute($request)
    {
        return  $request->route()->getActionName() == 'omenUpload';
    }

    private function addCSRFTokenToResponse(&$response)
    {
        $response->header(CSRF::getHeaderName(), CSRF::generate());
    }

    private function checkOmenCSRFToken($request)
    {
        $omenCSRF = CSRF::check($request);
        return $omenCSRF;
    }

    private function checkLaravelCSRFToken($request)
    {
        $sessionToken = session()->token();
        $header = $request->header('X-CSRF-TOKEN');
        return $sessionToken == $header;
    }
}
