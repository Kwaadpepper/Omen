<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Lib\ImageLib;

class ImageController extends Controller
{
    public function resize(Request $request)
    {
        if (
            !$request->filled('filepath') or
            !$request->filled('fileheight') or
            !$request->filled('filewidth')
        ) {
            abort(400);
        }

        if (
            !\filter_var($request->post('fileheight'), FILTER_VALIDATE_FLOAT) or
            !\filter_var($request->post('filewidth'), FILTER_VALIDATE_FLOAT)
        ) {
            abort(400);
        }

        $fm = new FileManager();
        $filepath = OmenHelper::uploadPath($request->post('filepath'));

        if (!$fm->exists($filepath)) {
            abort(404);
        }

        $inode = $fm->inode($filepath);
        $newInode = null;
        if ($request->filled('new')) {
            $newInode = $fm->inode($fm->getNewFileName($inode->getFullPath()));
        }

        if (!ImageLib::resize(
            $inode,
            \floatval($request->post('filewidth')),
            \floatval($request->post('fileheight')),
            $newInode
        )) {
            abort(500);
        }

        if ($newInode) {
            $inode = $newInode;
        }

        return response()->json($inode, 200);
    }

    public function crop(Request $request)
    {
        if (
            !$request->filled('filepath') or
            !$request->filled('x') or
            !$request->filled('y') or
            !$request->filled('width') or
            !$request->filled('height') or
            !$request->filled('rotate') or
            !$request->filled('scaleX') or
            !$request->filled('scaleY')
        ) {
            abort(400);
        }

        $cropX = $request->post('x');
        $cropY = $request->post('y');
        $width = $request->post('width');
        $height = $request->post('height');
        $rotate = $request->post('rotate');
        $sx = $request->post('scaleX');
        $sy = $request->post('scaleY');

        if (
            !\is_numeric($cropX) or
            !\is_numeric($cropY) or
            !\is_numeric($width) or
            !\is_numeric($height) or
            !\is_numeric($rotate) or
            !\is_numeric($sx) or
            !\is_numeric($sy)
        ) {
            abort(400);
        }

        // Intervention crop only supports integer values
        $cropX = \intval($request->post('x'));
        $cropY = \intval($request->post('y'));
        $width = \intval($request->post('width'));
        $height = \intval($request->post('height'));
        $rotate = \intval($request->post('rotate'));
        $sx = \intval($request->post('scaleX'));
        $sy = \intval($request->post('scaleY'));

        $fm = new FileManager();
        $filepath = OmenHelper::uploadPath($request->post('filepath'));

        if (!$fm->exists($filepath)) {
            abort(404);
        }

        $inode = $fm->inode($filepath);
        $newInode = null;
        if ($request->filled('new')) {
            $newInode = $fm->inode($fm->getNewFileName($inode->getFullPath()));
        }

        $operations = [];

        // Rotation function
        if ($rotate) {
            $operations[] = ['function' => 'rotate', 'args' => ['angle' => $rotate]];
        }
        // Flip / Scale functions
        if ($sx == -1 and $sy == -1) {
            // flip vertical
            $operations[] = ['function' => 'flip', 'args' => ['vertical' => true]];
            // flip horizontal
            $operations[] = ['function' => 'flip'];
        } else if ($sx == 1 and $sy == -1) {
            // flip vertical
            $operations[] = ['function' => 'flip', 'args' => ['vertical' => true]];
        } else if ($sx == -1 and $sy == 1) {
            // flip horizontal
            $operations[] = ['function' => 'flip'];
        }

        $operations[] = [
            'function' => 'crop',
            'args' => [
                'cropX' => $cropX,
                'cropY' => $cropY,
                'width' => $width,
                'height' => $height
            ]
        ];

        if (!ImageLib::applyOn($inode, $operations, $newInode)) {
            abort(500);
        }

        if ($newInode) {
            $inode = $newInode;
        }

        return response()->json($inode, 200);
    }
}
