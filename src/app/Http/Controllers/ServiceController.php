<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use Error;
use Illuminate\Http\Request;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\OmenHelper;

class ServiceController
{
    public function ping()
    {
        return response()->json();
    }

    public function log(Request $request)
    {
        if (!$request->filled('code') || !$request->filled('message')) {
            return OmenHelper::abort(404);
        }
        try {
            report(new OmenException(\sprintf('FrontEND : %s', $request->post('message')), $request->post('code')));
        } catch (Error $e) {
            report(new OmenException(
                \sprintf(
                    'Error while adding to log frontend error  code  => "%s"  message => "%s"',
                    $request->post('code'),
                    $request->post('message')
                ),
                $e
            ));
        }

        return response()->json(['status' => 'OK']);
    }

    public function cspReport(Request $request)
    {
        $jsonRequest = json_decode($request->getContent(), true);
        if (empty($jsonRequest['csp-report'])) {
            return OmenHelper::abort(404);
        }
        try {
            report(new OmenException(\sprintf('Omen CSP violation report %s', OmenHelper::formatCspReport($jsonRequest))));
        } catch (Error $e) {
            report(new OmenException(
                \sprintf(
                    'Error while adding to log frontend error  code  => "%s"  message => "%s"',
                    $request->post('code'),
                    $request->post('message')
                ),
                $e
            ));
        }

        return response()->json(['status' => 'OK']);
    }
}
