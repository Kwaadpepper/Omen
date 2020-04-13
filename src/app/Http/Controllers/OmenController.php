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
use Kwaadpepper\Omen\Lib\Disk;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as ExceptionFileNotFoundException;

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


        return view('omen::interface', \compact('inodes', 'path', 'query'));
    }

    /**
     * Handles resumables Uploads per one file at a time
     * 
     * write chunks in temp files (1,2,3 etc)
     * and then combine them
     * 
     * TODO check all exceptions
     */
    public function upload(Request $request)
    {
        // return response()->json(['error' => 'Bad Request'], 500);

        $answer = [];

        // Request CHECKS

        // check method and params
        if (
            !$request->hasFile('fileBlob')
            or $request->post('fileName', false) === false
            or $request->post('filePath', false) === false
            or $request->post('fileSize', false) === false
            or $request->post('fileId', false) === false
            or $request->post('chunkIndex', false) === false
            or $request->post('chunkCount', false) === false
        ) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        $file = $request->file('fileBlob');
        $filePath = OmenHelper::sanitizePath(sprintf('%s/%s', $request->post('filePath'), $request->post('fileName')));
        $directoryPath = OmenHelper::mb_pathinfo($filePath, \PATHINFO_DIRNAME);
        $fileName = OmenHelper::mb_pathinfo($filePath, \PATHINFO_BASENAME);
        $fileSize = $request->post('fileSize');
        $fileId = $request->post('fileId');
        $chunkIndex = $request->post('chunkIndex');
        $totalChunks = $request->post('chunkCount');
        $omenTempPath = 'tmp';

        // return response()->json([
        //     'chunkIndex' => $chunkIndex,
        //     'error' => 'Bad Request'
        // ], 500);

        // chunk file size is not equal request filesize
        // if (
        //     $file->getSize() != $request->post('fileSize')
        //     or $file->getClientOriginalName() != $request->post('fileName')
        // ) {
        //     return response()->json(['error' => 'Bad Request'], 400);
        // }


        $fm = new FileManager();

        // Check if file exists in destination disk
        if ($fm->exists(OmenHelper::uploadPath($filePath))) {
            // need to rename file !!!
            $filePath = $fm->getNewFileName($filePath);
            $fileName = OmenHelper::mb_pathinfo($filePath, \PATHINFO_BASENAME);
        }

        // upload Session setter
        $uploadSession = sprintf("omen.uploads.%s", base64_encode($filePath));
        // save timestamp to clear cache afterwards
        session()->put("$uploadSession.timestamp", time());
        session()->put("$uploadSession.totalChunks", $totalChunks);
        if (!session()->has("$uploadSession.chunks")) {
            session()->put("$uploadSession.chunks", []);
        }

        $sessionChunks = session()->get("$uploadSession.chunks");

        $failure = false;
        try {
            // check if destination dir exists
            if (!$fm->exists(OmenHelper::uploadPath($directoryPath))) {
                // create destination dir
                $fm->createDirectory(OmenHelper::uploadPath($directoryPath));
            }

            $fm->switchToDisk(Disk::PRIVATE);

            // check if work dir exists
            if (!$fm->exists(OmenHelper::privatePath($omenTempPath))) {
                // create work dir
                $fm->createDirectory(OmenHelper::privatePath($omenTempPath));
            }

            $chunkFileName = \base64_encode($fileId . $fileName);

            // if uploaded file is a chunk
            if ($totalChunks > 1) {
                $chunkFileName .= '_' . str_pad($chunkIndex, 4, '0', STR_PAD_LEFT);
            }

            $chunkFilePath = OmenHelper::privatePath("$omenTempPath/$chunkFileName");

            // move to local tmp dir
            $tmpInode = $fm->inode($chunkFilePath);
            $tmpInode->put($file->get());

            // save chunk to session
            $sessionChunks[$chunkIndex] = $chunkFilePath;
            session()->put("$uploadSession.chunks", $sessionChunks);
        } catch (OmenException $e) {
            $failure = true;
            report($e);
            return response()->json([
                'chunkIndex' => $chunkIndex,
                'error' => 'Bad Request'
            ], 400);
        } catch (FileException $e) {
            $failure = true;
            $exception = new OmenException('File manipulation error', '87' . __LINE__, $e);
            \report($exception);
            return response()->json([
                'chunkIndex' => $chunkIndex,
                'error' => 'Server Error, check logs'
            ], 500);
        } finally {
            // if exception occured then try to clean the chunks
            if ($failure) {
                try {
                    $chunckFiles = $fm->globFiles(
                        OmenHelper::privatePath($omenTempPath),
                        sprintf('%s.*', \base64_encode($fileId . $fileName))
                    );
                    foreach ($chunckFiles as $chunkFilePath) {
                        $fm->inode($chunkFilePath)->delete();
                    }
                } finally {
                    /**
                     *? giveup: chunks would have to be cleaned manualy ? 
                     */
                }
            }
        }

        $chunckFiles = $fm->globFiles(OmenHelper::privatePath($omenTempPath), sprintf('%s.*', \base64_encode($fileId . $fileName)));

        // check if all chunks are uploaded
        if (\count($chunckFiles) == $totalChunks) {

            // Clear Session upload
            session()->put($uploadSession, null);

            // assemble all chunks in private path
            $outFile = $fm->inode(OmenHelper::privatePath($filePath));

            foreach ($chunckFiles as $chunkFilePath) {
                $chunkInode = $fm->inode($chunkFilePath);

                try {

                    $content = $chunkInode->get();
                    $length = \strlen($content);

                    // append chunk Inode to temp out file
                    $outFile->append($chunkInode->get());
                } catch (ExceptionFileNotFoundException $e) {
                    $exception = new OmenException('File manipulation error', '87' . __LINE__, $e);
                    \report($exception);
                    return response()->json([
                        'chunkIndex' => $chunkIndex,
                        'error' => 'Server Error, check logs'
                    ], 500);
                } finally {
                    // remove chunk Inode
                    $chunkInode->delete();
                }
            }

            // TODO check the final assembled file mimeType
            // if (
            //     $file->getSize() != $request->post('fileSize')
            //     or $file->getClientOriginalName() != $request->post('fileName')(new MimeTypes())->getMimeTypes($this->getExtension())
            //     or in_array($file->getMimeType(), OmenHelper::getAllowedFilesExtensions())
            // ) {
            //     return response()->json(['error' => 'Bad Request'], 400);
            // }

            $fm->switchToDisk(Disk::PUBLIC);

            // TODO upload the tmp file to storage
            try {
                $uploadedInode = $fm->inode(OmenHelper::uploadPath($filePath));
                $uploadedInode->put($outFile->get());
                $outFile->delete();
            } catch (OmenException $e) {
                report($e);
                return response()->json(['error' => 'Server Error, check logs'], 500);
            } catch (ExceptionFileNotFoundException $e) {
                $exception = new OmenException('File manipulation error', '87' . __LINE__, $e);
                \report($exception);
                return response()->json([
                    'chunkIndex' => $chunkIndex,
                    'error' => 'Server Error, check logs'
                ], 500);
            }
        }

        return response()->json([
            'chunkIndex' => $chunkIndex // the chunk index processed
        ]);
    }

    // generate and fetch thumbnail for the file
    private function getThumbnailUrl($path, $fileName)
    {
        // assuming this is an image file or video file
        // generate a compressed smaller version of the file
        // here and return the status
        $sourceFile = $path . '/' . $fileName;
        $targetFile = $path . '/thumbs/' . $fileName;
        //
        // generateThumbnail: method to generate thumbnail (not included)
        // using $sourceFile and $targetFile
        //
        if ($this->generateThumbnail($sourceFile, $targetFile) === true) {
            return 'http://localhost/uploads/thumbs/' . $fileName;
        } else {
            return 'http://localhost/uploads/' . $fileName; // return the original file
        }
    }

    public function rename(Request $request)
    {
        if (!$request->filled('filename') or !$request->filled('filepath')) {
            abort(400);
        }

        $fm = new FileManager();

        $filepath = OmenHelper::uploadPath($request->post('filepath'));
        $filename = OmenHelper::sanitizePath($request->post('filename'));

        if (!$fm->exists($filepath)) {
            abort(404);
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
            abort(400);
        }

        $fm = new FileManager();

        $filepath = OmenHelper::uploadPath($request->post('filepath'));

        if (!$fm->exists($filepath)) {
            abort(404);
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

    public function createTextFile(Request $request)
    {
        // only filepath and filename must be filled
        if (!$request->filled('filepath') or !$request->has('filetext')) {
            abort(400);
        }

        $filepath = OmenHelper::sanitizePath(OmenHelper::uploadPath($request->post('filepath')));
        $filetext = $request->post('filetext');

        $fm = new FileManager();
        $inode = $fm->inode($filepath);
        try {
            if (!$inode->put($filetext)) {
                abort(500);
            }
        } catch (OmenException $e) {
            abort(400);
        }

        return response()->json($inode, 200);
    }

    public function createDirectory(Request $request)
    {
        // directorypath must be filled
        if (!$request->filled('directorypath')) {
            abort(400);
        }

        $directorypath = OmenHelper::sanitizePath(OmenHelper::uploadPath($request->post('directorypath')));

        $fm = new FileManager();

        try {
            $fm->createDirectory($directorypath);
        } catch (OmenException $e) {
            report($e);
            abort(400);
        }

        $inode = $fm->inode($directorypath);

        return response()->json($inode, 200);
    }

    public function getInodeHtml(Request $request)
    {
        if (!$request->filled('filepath') and !$request->filled('directorypath')) {
            abort(400);
        }

        $inodepath = !empty($request->post('filepath')) ? $request->post('filepath') : $request->post('directorypath');

        $inodepath = OmenHelper::uploadPath($inodepath);

        $fm = new FileManager();

        if (!$fm->exists($inodepath)) {
            abort(404);
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

        return response()->json(['inodeHtml' => $view->render()], 200);
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
            abort(404);
        }
    }

    public function cspReport(Request $request)
    {
        $jsonRequest = json_decode($request->getContent(), true);
        if (empty($jsonRequest['csp-report'])) {
            abort(404);
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

    public function log(Request $request)
    {
        if (!$request->filled('code') || !$request->filled('message')) {
            abort(404);
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
