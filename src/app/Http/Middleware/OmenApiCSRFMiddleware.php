<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Closure;
use Exception;
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
            // omenUpload can't be handled yet since its parallels requests
            if (!CSRF::check($request) and $request->route()->getName() != "omenUpload") {
                return \response('', 401);
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
}
