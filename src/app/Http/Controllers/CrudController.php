<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;

class CrudController extends Controller
{
    public function createDirectory(Request $request)
    {
        if (!$request->exists('directoryPath') or !$request->filled('directoryName')) {
            return OmenHelper::abort(400);
        }

        $filename = OmenHelper::filterFilename($request->post('directoryName'));
        $directoryPath = OmenHelper::uploadPath(sprintf('%s/%s', $request->post('directoryPath'), $filename));
        if (\strlen($filename) < config('omen.minimumInodeLength', 3)) {
            return response()->json([
                'message' => __('Directory name must be at least :length long', [
                    'length' => config('omen.minimumInodeLength', 3)
                ]),
                'filename' => $filename
            ], 400);
        }


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
        if (!$request->filled('inodename') or !$request->filled('inodepath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $inodepath = OmenHelper::uploadPath($request->post('inodepath'));
        $inodename = OmenHelper::filterFilename($request->post('inodename'));

        if (!$fm->exists($inodepath)) {
            return OmenHelper::abort(404);
        }

        $inode = $fm->inode($inodepath);

        if (!\strlen($inode->getExtension())) {
            $inodename = \str_replace('.', '-', $inodename);
        }

        if (\strlen($inodename) < 3) {
            return response()->json([
                'message' => __('Name must be at least :length long', [
                    'length' => config('omen.minimumInodeLength', 3)
                ]),
                'inodename' => $inodename
            ], 400);
        }

        try {
            $inode->setFullName($inodename);
        } catch (Error $e) {
            report(new OmenException(
                \sprintf(
                    'Error while changing inode name  code  => "%s"  message => "%s"',
                    $request->post('code'),
                    $request->post('message')
                ),
                $e
            ));
        }

        return response()->json($inode);
    }

    public function delete(Request $request)
    {
        if (!$request->filled('inodepath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $inodepath = OmenHelper::uploadPath($request->post('inodepath'));

        if (!$fm->exists($inodepath)) {
            return OmenHelper::abort(404);
        }

        try {
            $fm->inode($inodepath)->delete();
        } catch (Error $e) {
            report(new OmenException(
                \sprintf(
                    'Error while deleting inode name  code  => "%s"  message => "%s"',
                    $request->post('code'),
                    $request->post('message')
                ),
                $e
            ));
            return response()->json([], 500);
        }
        return response()->json();
    }

    public function copyto(Request $request)
    {
        if (!$request->filled('sourcePath') or !$request->filled('destPath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $sourcePath = OmenHelper::uploadPath($request->post('sourcePath'));
        $destPath = OmenHelper::uploadPath($request->post('destPath'));

        if (!$fm->exists($sourcePath) || !$fm->exists($destPath)) {
            return OmenHelper::abort(404);
        }

        $sourceInode = $fm->inode($sourcePath);
        $destInode = $fm->inode($destPath);

        if ($destInode->getType() != InodeType::DIR || $sourceInode->getType() == InodeType::DIR) {
            return OmenHelper::abort(400);
        }

        $destInode = $fm->inode(sprintf('%s/%s', $destPath, $sourceInode->getFullName()));

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
        return response()->json($inode, 200);
    }

    public function moveto(Request $request)
    {
        if (!$request->filled('sourcePath') or !$request->filled('destPath')) {
            return OmenHelper::abort(400);
        }

        $fm = new FileManager();

        $sourcePath = OmenHelper::uploadPath($request->post('sourcePath'));
        $destPath = OmenHelper::uploadPath($request->post('destPath'));

        if (!$fm->exists($sourcePath) || !$fm->exists($destPath)) {
            return OmenHelper::abort(404);
        }

        $sourceInode = $fm->inode($sourcePath);
        $destInode = $fm->inode($destPath);

        if ($destInode->getType() != InodeType::DIR) {
            return OmenHelper::abort(400);
        }

        $destInode = $fm->inode(sprintf('%s/%s', $destPath, $sourceInode->getFullName()));

        $inode = null;
        try {
            $inode = $fm->moveTo($sourceInode, $destInode);
        } catch (OmenException $e) {
            report($e);
            return OmenHelper::abort(500);
        } catch (\League\Flysystem\FileExistsException $e) {
            return response()->json([
                'message' => __('Cannot move element already exists')
            ], 409);
        }
        return response()->json($inode, 200);
    }
}
