<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kwaadpepper\Omen\Lib\CSRF;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;

class OmenController extends Controller
{

    public function index(Request $request)
    {
        $path  = Session::get('omen.path');
        $fm = new FileManager();
        $inodes = $fm->inodes(OmenHelper::uploadPath($path));

        $query = [
            'path' => Session::get('omen.path'),
            'locale' => Session::get('omen.locale')
        ];

        $apiCSRFToken = [
            'name' => CSRF::getHeaderName(),
            'key' => CSRF::generate()
        ];

        config(['omen.CSRFTokenKey' => CSRF::getHeaderName()]);

        return view('omen::interface', \compact('inodes', 'path', 'query', 'apiCSRFToken'));
    }
}
