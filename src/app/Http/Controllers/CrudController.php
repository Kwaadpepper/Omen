<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use Error;
use Illuminate\Http\Request;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;

class CrudController
{
    public function createDirectory(Request $request)
    {
        if (!$request->filled('directoryPath') or !$request->filled('directoryName')) {
            return OmenHelper::abort(400);
        }

        $directoryPath = OmenHelper::uploadPath(sprintf('%s/%s', $request->post('directoryPath'), OmenHelper::filterFilename($request->post('directoryName'))));

        $fm = new FileManager();

        try {
            $fm->createDirectory($directoryPath);
        } catch (OmenException $e) {
            report($e);
            return OmenHelper::abort(400);
        }

        $inode = $fm->inode($directoryPath);

        return response()->json($inode, 200);
    }

    public function rename(Request $request)
    {
        if (!$request->filled('filename') or !$request->filled('filepath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $filepath = OmenHelper::uploadPath($request->post('filepath'));
        $filename = OmenHelper::filterFilename($request->post('filename'));

        if (!$fm->exists($filepath)) {
            return OmenHelper::abort(404);
        }

        $inode = $fm->inode($filepath);

        try {
            $inode->setFullName($filename);
        } catch (Error $e) {
            report(new OmenException(
                \sprintf(
                    'Error while changing inode name  code  => "%s"  message => "%s"',
                    $request->post('code'),
                    $request->post('message')
                ),
                '9' + __LINE__,
                $e
            ));
        }

        return response()->json($inode);
    }

    public function delete(Request $request)
    {
        if (!$request->filled('filepath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $filepath = OmenHelper::uploadPath($request->post('filepath'));

        if (!$fm->exists($filepath)) {
            return OmenHelper::abort(404);
        }

        try {
            $fm->inode($filepath)->delete();
        } catch (Error $e) {
            report(new OmenException(
                \sprintf(
                    'Error while deleting inode name  code  => "%s"  message => "%s"',
                    $request->post('code'),
                    $request->post('message')
                ),
                '9' + __LINE__,
                $e
            ));
        }
        return response()->json(['status' => 'OK']);
    }

    public function copyto(Request $request)
    {
        if (!$request->filled('sourcePath') or !$request->filled('destPath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $sourcePath = OmenHelper::uploadPath($request->post('sourcePath'));
        $destPath = OmenHelper::uploadPath($request->post('destPath'));

        if (!$fm->exists($sourcePath) or !$fm->exists($destPath)) {
            return OmenHelper::abort(404);
        }

        $sourceInode = $fm->inode($sourcePath);
        $destInode = $fm->inode($destPath);

        if ($destInode->getType() != InodeType::DIR) {
            return OmenHelper::abort(400);
        }
        $inode = null;
        try {
            $inode = $fm->copyTo($sourceInode, $destInode);
        } catch (OmenException $e) {
            report($e);
            return OmenHelper::abort(500);
        } catch (\League\Flysystem\FileExistsException $e) {
            return response()->json([
                'message' => __('Cannot copy element already exists')
            ], 409);
        }
        return response()->json([
            'inode' => $inode
        ], 200);
    }

    public function moveto(Request $request)
    {
        if (!$request->filled('sourcePath') or !$request->filled('destPath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $sourcePath = OmenHelper::uploadPath($request->post('sourcePath'));
        $destPath = OmenHelper::uploadPath($request->post('destPath'));

        if (!$fm->exists($sourcePath) or !$fm->exists($destPath)) {
            return OmenHelper::abort(404);
        }

        $sourceInode = $fm->inode($sourcePath);
        $destInode = $fm->inode($destPath);

        if ($destInode->getType() != InodeType::DIR) {
            return OmenHelper::abort(400);
        }

        try {
            $fm->moveTo($sourceInode, $destInode);
        } catch (OmenException $e) {
            report($e);
            return OmenHelper::abort(500);
        } catch (\League\Flysystem\FileExistsException $e) {
            return response()->json([
                'message' => __('Cannot move element already exists')
            ], 409);
        }
        return response()->json(true, 200);
    }
}
