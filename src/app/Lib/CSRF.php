<?php

namespace Kwaadpepper\Omen\Lib;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CSRF
{

    public static function getHeaderName()
    {
        return 'omen-csrf-token';
    }

    private static function getToken()
    {
        return request()->session()->get('omenCSRFToken');
    }
    private static function storeToken($token)
    {
        request()->session()->put('omenCSRFToken', $token);
        request()->session()->save();
    }

    public static function generate()
    {
        $token = Str::random(32);
        static::storeToken($token);
        return $token;
    }

    public static function check(Request $request)
    {
        if (($token = $request->header(static::getHeaderName())) == null) {
            return false;
        }
        $saveToken = static::getToken();
        if ($saveToken != $token) {
            return false;
        }
        return true;
    }
}
