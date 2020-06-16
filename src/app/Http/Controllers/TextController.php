<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        $filePath = OmenHelper::uploadPath(sprintf('%s/%s', $request->post('filePath'), OmenHelper::filterFilename($request->post('fileName'))));
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
