<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\Disk;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as ExceptionFileNotFoundException;
use Symfony\Component\Mime\MimeTypes;

class UploadController extends Controller
{
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
        $filePath = OmenHelper::sanitizePath(sprintf('%s/%s', $request->post('filePath'), OmenHelper::filterFilename($request->post('fileName'))));
        $directoryPath = OmenHelper::mb_pathinfo($filePath, \PATHINFO_DIRNAME);
        $fileName = OmenHelper::mb_pathinfo($filePath, \PATHINFO_BASENAME);
        $fileSize = $request->post('fileSize');
        $fileId = $request->post('fileId');
        $chunkIndex = $request->post('chunkIndex');
        $totalChunks = $request->post('chunkCount');
        $omenTempPath = 'tmp';

        // check announced file size
        if (config('omen.maxUploadSize') and $fileSize > \intval(\rtrim(config('omen.maxUploadSize'), 'M'))) {
            return response()->json([
                'chunkIndex' => $chunkIndex,
                'error' => __('File too large')
            ], 400);
        }

        $fm = new FileManager();

        // Check if file exists in destination disk
        if ($fm->exists(OmenHelper::uploadPath($filePath))) {
            // need to rename file !!!
            $filePath = $fm->getNewFileName(OmenHelper::uploadPath($filePath));
            $filePath = substr($filePath, \strlen(OmenHelper::uploadPath('')), \strlen($filePath));
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

        function memory_usage()
        {
            $mem_usage = memory_get_usage(true);
            if ($mem_usage < 1024) {
                $mem_usage .= ' bytes';
            } elseif ($mem_usage < 1048576) {
                $mem_usage = round($mem_usage / 1024, 2) . ' kilobytes';
            } else {
                $mem_usage = round($mem_usage / 1048576, 2) . ' megabytes';
            }
            return $mem_usage;
        };

        // check if all chunks are uploaded
        if (\count($chunckFiles) == $totalChunks) {

            $clearChunks = function () use ($chunckFiles, $fm) {
                foreach ($chunckFiles as $chunkFilePath) {
                    $chunkInode = $fm->inode($chunkFilePath);
                    try {
                        $chunkInode->delete();
                    } catch (OmenException $e) {
                        // ignore
                    }
                }
            };

            // Clear Session upload
            session()->put($uploadSession, null);

            // assemble all chunks in private path
            $outFile = $fm->inode(OmenHelper::privatePath($filePath));
            $outFileSize = 0;

            // check if file exists and delete before building it
            if ($fm->exists($outFile)) {
                $outFile->delete();
            }

            try {
                foreach ($chunckFiles as $chunkFilePath) {
                    $chunkInode = $fm->inode($chunkFilePath);
                    $d = $chunkInode->get();
                    $outFileSize += \strlen($d);
                    // append chunk Inode to temp out file
                    $outFile->append($d);
                    unset($d);
                    $chunkInode->delete();
                }
            } catch (ExceptionFileNotFoundException $e) {
                $clearChunks();
                $exception = new OmenException('File manipulation error', '87' . __LINE__, $e);
                \report($exception);
                return response()->json([
                    'chunkIndex' => $chunkIndex,
                    'error' => 'Server Error, check logs'
                ], 500);
            }

            // check real assembled file size
            if ($outFileSize != $fileSize) {
                $clearChunks();
                return response()->json([
                    'chunkIndex' => $chunkIndex,
                    'error' => __('Uploaded file was truncated, missing data')
                ], 400);
            }
            if (config('omen.maxUploadSize') and $outFileSize > \intval(\rtrim(config('omen.maxUploadSize'), 'M'))) {
                $clearChunks();
                return response()->json([
                    'chunkIndex' => $chunkIndex,
                    'error' => __('Uploaded file is too large')
                ], 400);
            }

            // check the final assembled file mimeType
            $mimeTypeChecker = new MimeTypes();
            $mimeType = null;
            if ($mimeTypeChecker->isGuesserSupported()) {
                $mimeType = $mimeTypeChecker->guessMimeType(storage_path(\sprintf('app%s%s', \DIRECTORY_SEPARATOR, $outFile->getFullPath())));
                if (!\count(\array_intersect($mimeTypeChecker->getExtensions($mimeType), OmenHelper::getAllowedFilesExtensions()))) {
                    return response()->json(['error' => __('File type not allowed')], 400);
                }
            }

            $fm->switchToDisk(Disk::PUBLIC);

            // TODO upload the tmp file to storage
            try {
                $uploadedInode = $fm->inode(OmenHelper::uploadPath($filePath));
                $uploadedInode->put($outFile->get());
                $outFile->delete();
                session()->save();
                return response()->json([
                    'chunkIndex' => $chunkIndex, // the chunk index processed
                    'filename' => $fileName,
                    'inode' => $uploadedInode
                ]);
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

        session()->save();

        return response()->json([
            'chunkIndex' => $chunkIndex // the chunk index processed
        ]);
    }
}
