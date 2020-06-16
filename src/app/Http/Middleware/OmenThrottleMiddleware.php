<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class OmenThrottleMiddleware extends ThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        $routeName = $request->route()->getName();
        $routeGroupName = \substr($routeName, 0, \strpos($routeName, '.'));
        return $routeGroupName . parent::resolveRequestSignature($request);
    }
}
