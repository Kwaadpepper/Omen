<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use App\Http\Controllers\Controller;
use Error;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\CSRF;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;

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

    public function download(Request $request, $filePath)
    {
        $fm = new FileManager();

        if ($request->method('HEAD')) {
            if (!$fm->exists(OmenHelper::uploadPath($filePath)))
                abort(404);
        }

        try {
            return $fm->inode(OmenHelper::uploadPath($filePath))->download();
        } catch (FlysystemFileNotFoundException $e) {
            abort(404);
        }
    }

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

    public function getInodeHtml(Request $request)
    {
        if (!$request->filled('filepath') and !$request->filled('directorypath')) {
            return OmenHelper::abort(400);
        }

        $inodepath = !empty($request->post('filepath')) ? $request->post('filepath') : $request->post('directorypath');

        $inodepath = OmenHelper::uploadPath($inodepath);

        $fm = new FileManager();

        if (!$fm->exists($inodepath)) {
            return OmenHelper::abort(404);
        }

        $inode = $fm->inode($inodepath);
        $view = view(
            sprintf('omen::elements.inodesView.%s', $inode->getType()),
            [
                'inode' => $inode,
                'id' => sha1($inode->getFullPath()),
                'inodeType' => $inode->getType()
            ]
        );

        return response()->json(['inode' => $inode, 'inodeHtml' => $view->render()], 200);
    }

    public function getInodesAtPath(Request $request)
    {
        if (!$request->filled('path')) {
            return OmenHelper::abort(400);
        }

        $inodepath = OmenHelper::uploadPath($request->get('path'));
        $fm = new FileManager();

        if (!$fm->exists($inodepath)) {
            return OmenHelper::abort(404);
        }

        $inodes = $fm->inodes($inodepath);
        $view = view(
            'omen::elements.inodesView.view',
            [
                'inodes' => $inodes,
                'path' => $request->get('path')
            ]
        );

        return response()->json([
            'inodes' => $inodes,
            'inodesHtml' => $view->render()
        ], 200);
    }

    public function getBreadcrumbAtPath(Request $request)
    {
        if (!$request->filled('path')) {
            return OmenHelper::abort(400);
        }

        $inodepath = OmenHelper::uploadPath($request->get('path'));
        $fm = new FileManager();

        if (!$fm->exists($inodepath)) {
            return OmenHelper::abort(404);
        }

        // session should be filled with path request
        $query = [
            'path' => Session::get('omen.path'),
            'locale' => Session::get('omen.locale')
        ];

        $view = view(
            'omen::elements.breadcrumb',
            [
                'path' => $request->get('path'),
                'query' => $query
            ]
        );

        return response()->json([
            'breadcrumbHtml' => $view->render()
        ], 200);
    }

    /**
     * Serve asset if not published
     */
    public function asset(Request $request, string $fileUri)
    {
        try {
            $filePath = __DIR__ . '/../../../../resources/' . $fileUri;
            $fileContent = File::get($filePath);
            $fileExt = OmenHelper::mb_pathinfo($filePath, PATHINFO_EXTENSION);

            //fix for missing mimetypes in https://github.com/guzzle/psr7/blob/master/src/functions.php
            // https://www.iana.org/assignments/media-types/media-types.xhtml
            $mimeFix = [
                'woff2' => 'application/font-woff2',
                'woff' => 'application/font-woff',
                'ttf' => 'application/font-ttf',
                'sfnt' => 'application/font-sfnt',
                'otf' => 'application/font-otf',
                'collection' => 'application/font-collection'
            ];

            $mimeType = $mimeFix[$fileExt] ?? MimeType::from($filePath);
            $fileSize = File::size($filePath);
            return response($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Size' => $fileSize
            ]);
        } catch (FileNotFoundException $e) {
            return OmenHelper::abort(404);
        }
    }

    public function cspReport(Request $request)
    {
        $jsonRequest = json_decode($request->getContent(), true);
        if (empty($jsonRequest['csp-report'])) {
            return OmenHelper::abort(404);
        }
        try {
            report(new OmenException(\sprintf('Omen CSP violation report %s', OmenHelper::formatCspReport($jsonRequest)), '23' . __LINE__));
        } catch (Error $e) {
            report(new OmenException(
                \sprintf(
                    'Error while adding to log frontend error  code  => "%s"  message => "%s"',
                    $request->post('code'),
                    $request->post('message')
                ),
                '9' + __LINE__,
                $e
            ));
        }

        return response()->json(['status' => 'OK']);
    }

    public function ping(Request $request)
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
                '9' + __LINE__,
                $e
            ));
        }

        return response()->json(['status' => 'OK']);
    }
}
