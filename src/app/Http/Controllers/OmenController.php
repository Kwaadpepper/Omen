<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\CSRF;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;

class OmenController extends Controller
{

    public function index(Request $request)
    {
        if (!\rand(0, 10)) {
            $this->cleanPrivateUploadDir();
        }

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

    private function cleanPrivateUploadDir()
    {
        $fm = new FileManager();
        $fileTimeoutToClean = 1 * 24 * 60 * 60; // 1 day

        // uploaded files
        $uFiles = Storage::allFiles(OmenHelper::privatePath('out'));

        foreach ($uFiles as $file) {
            $inode = $fm->inode($file);
            //* check file is older than 1 day *//
            if ($inode->getLastModfied() + $fileTimeoutToClean < \time()) {
                try {
                    $inode->delete();
                } catch (OmenException $e) {
                    report(new OmenException(
                        sprintf('Upload clearner: could not remove orphan file %s', $inode->getFullPath()),
                        '58' . __LINE__,
                        $e
                    ));
                }
            }
        }


        // chunk files
        $ckFiles = Storage::allFiles(OmenHelper::privatePath('tmp'));

        foreach ($ckFiles as $file) {
            $inode = $fm->inode($file);
            //* check chunk file is older than 1 day *//
            if ($inode->getLastModfied() + $fileTimeoutToClean < \time()) {
                try {
                    $inode->delete();
                } catch (OmenException $e) {
                    report(new OmenException(
                        sprintf('Upload clearner: could not remove orphan chunk file %s', $inode->getFullPath()),
                        '59' . __LINE__,
                        $e
                    ));
                }
            }
        }
    }
}
