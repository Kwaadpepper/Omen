<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;

class TextController extends Controller
{

    public function createTextFile(Request $request)
    {
        // only filepath and filename must be filled
        if (!$request->filled('filePath') or !$request->filled('fileName') or !$request->has('fileText')) {
            return OmenHelper::abort(400);
        }

        $filename = OmenHelper::filterFilename($request->post('fileName'));
        $filePath = OmenHelper::uploadPath(sprintf('%s/%s.txt', $request->post('filePath'), $filename));
        $fileText = $request->post('fileText');
        $fileExt = OmenHelper::mb_pathinfo($filename, \PATHINFO_EXTENSION);
        $fb = \substr($filename, 0, \strlen($filename) - \strlen($fileExt) - (\strlen($fileExt) ? 1 : 0));
        $emptyFileName = ($filename == 'txt' and !\strlen($fileExt));
        if (\strlen($fb) < config('omen.minimumInodeLength', 3) or $emptyFileName) {
            return response()->json([
                'message' => __('File name must be at least :length long', [
                    'length' => config('omen.minimumInodeLength', 3)
                ]),
                'filename' => $emptyFileName ? '' : $fb
            ], 400);
        }

        $fm = new FileManager();
        $inode = $fm->inode($filePath);
        try {
            if (!$inode->put($fileText)) {
                return OmenHelper::abort(500);
            }
        } catch (OmenException $e) {
            return OmenHelper::abort(400);
        }

        return response()->json($inode, 200);
    }

    public function updateTextFile(Request $request)
    {
        // only filepath and filename must be filled
        if (!$request->filled('filePath') or !$request->has('fileText')) {
            return OmenHelper::abort(400);
        }

        $filePath = OmenHelper::uploadPath($request->post('filePath'));
        $fileText = $request->post('fileText');

        $fm = new FileManager();
        $inode = $fm->inode($filePath);
        try {
            if (!$inode->put($fileText)) {
                return OmenHelper::abort(500);
            }
        } catch (OmenException $e) {
            return OmenHelper::abort(400);
        }

        return response()->json([], 200);
    }
}
