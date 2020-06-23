<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\InodeVisibility;
use Kwaadpepper\Omen\OmenHelper;
use League\Flysystem\FileNotFoundException;

class DownloadController extends Controller
{
    public function download(Request $request, $filePath)
    {
        $fm = new FileManager();

        if ($request->method('HEAD')) {
            if (!$fm->exists(OmenHelper::uploadPath($filePath))) {
                return OmenHelper::abort(404);
            }
        }

        $inode = $fm->inode(OmenHelper::uploadPath($filePath));

        if (
            $inode->getVisibility() == InodeVisibility::PRIVATE and
            Session::token() != Cookie::get('XSRF-TOKEN')
        ) {
            throw new TokenMismatchException();
        }

        try {
            if ($request->has('view')) return $inode->response();
            else return $inode->download();
        } catch (FileNotFoundException $e) {
            return OmenHelper::abort(404);
        }
    }
}
