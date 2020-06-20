<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\Disk;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\Inode;
use Kwaadpepper\Omen\OmenHelper;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as ExceptionFileNotFoundException;
use Symfony\Component\Mime\MimeTypes;

class UploadController extends Controller
{
    private $fm;
    private $chunkIndex;
    private $chunkFiles;
    private $uploadSession;

    /**
     * Handles resumables Uploads per one file at a time
     * 
     * write chunks in temp files (1,2,3 etc)
     * and then combine them
     */
    public function upload(Request $request)
    {
        if ($this->checkUploadParams($request)) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        $filePath = OmenHelper::sanitizePath(
            sprintf(
                '%s/%s',
                $request->post('filePath'),
                OmenHelper::filterFilename($request->post('fileName'))
            )
        );
        $directoryPath = OmenHelper::mb_pathinfo($filePath, \PATHINFO_DIRNAME);
        $fileName = OmenHelper::mb_pathinfo($filePath, \PATHINFO_BASENAME);
        $fileSize = $request->post('fileSize');
        $fileId = $request->post('fileId');
        $this->chunkIndex = $request->post('chunkIndex');
        $totalChunks = $request->post('chunkCount');
        $omenTempPath = 'tmp';
        $this->fm = new FileManager();
        $failure = false;

        if (!$this->checkFileName($fileName)) {
            return $this->errorResponse(
                400,
                __('File name is too short ":filename", check it does not contain invalid chars and it is longer than 3 characters', ['filename' => $fileName])
            );
        }

        if ($this->checkFileSize($fileSize)) {
            return $this->errorResponse(400, __('File too large'));
        }

        if ($this->inodeExists(Disk::PUBLIC, $filePath)) {
            $filePath = $this->getNewFileName($filePath);
            $fileName = OmenHelper::mb_pathinfo($filePath, \PATHINFO_BASENAME);
        }

        $sessionChunks = $this->getSessionChunks($filePath);

        try {
            if (!$this->inodeExists(Disk::PUBLIC, $directoryPath)) {
                $this->createDirectory(Disk::PUBLIC, $directoryPath);
            }

            $this->fm->switchToDisk(Disk::PRIVATE);

            if (!$this->inodeExists(Disk::PRIVATE, $omenTempPath)) {
                $this->createDirectory(Disk::PRIVATE, $omenTempPath);
            }

            $chunkFileName = \base64_encode(sprintf('%s###%d###', $fileId, time()));

            //* if uploaded file is a chunk *//
            if ($totalChunks > 1) {
                $chunkFileName .= '_' . str_pad($this->chunkIndex, 4, '0', STR_PAD_LEFT);
            }

            $chunkFilePath = OmenHelper::privatePath("$omenTempPath/$chunkFileName");

            //* move to local tmp dir *//
            $this->saveTempInode($request, $chunkFilePath);

            //* save chunk to session *//
            $sessionChunks[$this->chunkIndex] = $chunkFilePath;
            $this->setSessionChunks($sessionChunks);
        } catch (OmenException $e) {
            $failure = true;
            report($e);
            return $this->errorResponse(400, __('Bad request'));
        } catch (FileException $e) {
            $failure = true;
            $exception = new OmenException('File manipulation error', '87' . __LINE__, $e);
            report($exception);
            return $this->errorResponse();
        } finally {
            try {
                //* Anyway get all chunk files *//
                $this->chunkFiles = $this->getAllInodes(
                    OmenHelper::privatePath($omenTempPath),
                    sprintf('%s.*', \base64_encode($fileId . $fileName))
                );

                //* If exception occured then try to clean the chunks *//
                if ($failure) {
                    foreach ($this->chunkFiles as $chunkFilePath) {
                        $this->fm->inode($chunkFilePath)->delete();
                    }
                }
            } catch (Exception $e) {
                report($e);
                //* Can't continue without chunk files *//
                if (!$this->chunkFiles) {
                    return $this->errorResponse();
                }
            } finally {
                //? Giveup: chunks would have to be cleaned manualy ?//
            }
        }

        //* If all chunks are uploaded *//
        if (\count($this->chunkFiles) == $totalChunks) {
            return $this->assembleUploadedFile($fileName, $filePath, $fileSize);
        }

        session()->save();

        return response()->json([
            'chunkIndex' => $this->chunkIndex
        ]);
    }

    /**
     * check if needed request params exists
     * @param Request $request 
     * @return bool 
     */
    private function checkUploadParams($request)
    {
        return !$request->hasFile('fileBlob')
            or $request->post('fileName', false) === false
            or $request->post('filePath', false) === false
            or $request->post('fileSize', false) === false
            or $request->post('fileId', false) === false
            or $request->post('chunkIndex', false) === false
            or $request->post('chunkCount', false) === false;
    }

    /**
     * Check if the file name has at least enough chars
     * according to config('omen.minimumFileLength') or 3
     * @param mixed $fileName 
     * @return bool 
     */
    private function checkFileName($fileName)
    {
        $ext = OmenHelper::mb_pathinfo($fileName, \PATHINFO_EXTENSION);
        $bn = \substr($fileName, 0, \strlen($fileName) - \strlen($ext) - (\strlen($ext) ? 1 : 0));
        if (\strlen($bn) < config('omen.minimumFileLength', 3)) {
            return false;
        }
        return true;
    }

    /**
     * Check if the file size is allowed,
     * return false if ok else it means the file size exceed allowed
     * @param mixed $fileSize 
     * @return bool
     */
    private function checkFileSize($fileSize)
    {
        return config('omen.maxUploadSize') and $fileSize > \intval(\rtrim(config('omen.maxUploadSize'), 'M'));
    }

    /**
     * Check if the inode exists on the specified disk
     * @param string $disk 
     * @param string $filePath 
     * @return bool 
     */
    private function inodeExists(string $disk, string $filePath)
    {
        if ($disk == Disk::PUBLIC) {
            return $this->fm->exists(OmenHelper::uploadPath($filePath));
        } else {
            return $this->fm->exists(OmenHelper::privatePath($filePath));
        }
    }

    /**
     * Get a new filename from the specified one
     * @param string $filePath 
     * @return string|false
     */
    private function getNewFileName(string $filePath)
    {
        $filePath =  $this->fm->getNewFileName(OmenHelper::uploadPath($filePath));
        return substr($filePath, \strlen(OmenHelper::uploadPath('')), \strlen($filePath));
    }

    /**
     * Create a directory on specified disk
     * @param string $disk 
     * @param string $filePath 
     * @return void 
     * @throws OmenException 
     */
    private function createDirectory(string $disk, string $filePath)
    {
        if ($disk == Disk::PUBLIC) {
            return $this->fm->createDirectory(OmenHelper::uploadPath($filePath));
        } else {
            return $this->fm->createDirectory(OmenHelper::privatePath($filePath));
        }
    }

    /**
     * Gives the json response for a given http error
     * @param int $code 
     * @param mixed|null $message 
     * @return JsonResponse 
     * @throws BindingResolutionException 
     */
    private function errorResponse($code = 500, $message = null)
    {
        $message = $message ?? __('Server Error, check logs');
        return response()->json([
            'chunkIndex' => $this->chunkIndex,
            'error' => $message
        ], $code);
    }

    /**
     * Removes any chunks, uploaded file and give the error response
     * @param Inode $file 
     * @param int $code 
     * @param mixed|null $message 
     * @return JsonResponse|void 
     * @throws BindingResolutionException 
     */
    private function cleanAndGetErrorResponse(Inode $file, $code = 500, $message = null)
    {
        try {
            $this->clearUploadSession();
            $this->clearChunks();
            $file->delete();
        } catch (OmenException $e) {
            \report($e);
        } finally {
            return $this->errorResponse($code, $message);
        }
    }

    /**
     * Save the request file Blob the the given path
     * @param Request $request 
     * @param mixed $chunkFilePath 
     * @return void 
     * @throws FileNotFoundException 
     * @throws OmenException 
     * @throws ExceptionFileNotFoundException 
     */
    private function saveTempInode(Request $request, string $chunkFilePath)
    {
        $f = $request->file('fileBlob');
        if ($f == null) {
            throw new OmenException(__('Could not get uploaded file'));
        }
        $tmpInode = $this->fm->inode($chunkFilePath);
        $tmpInode->put($request->file('fileBlob')->get($request->file('fileBlob')));
    }

    /**
     * Get all inodes in a directory which match the giveen pattern
     * @param string $dirPath 
     * @param Regex $fileNamePattern 
     * @return Array[String] 
     */
    private function getAllInodes(string $dirPath, string $fileNamePattern)
    {
        return $this->fm->globFiles($dirPath, $fileNamePattern);
    }

    /**
     * Tries to delete all given chunks paths
     * @throws OmenException 
     */
    private function clearChunks()
    {
        foreach ($this->chunkFiles as $chunkFilePath) {
            $chunkInode = $this->fm->inode($chunkFilePath);
            $chunkInode->delete();
        }
        $this->setSessionChunks([]);
        session()->save();
    }

    private function getUploadSession(string $filePath)
    {
        //* upload Session setter *//
        $this->uploadSession = sprintf("omen.uploads.%s", base64_encode($filePath));

        //* save timestamp to clear cache afterwards *//
        session()->put("$this->uploadSession.timestamp", time());
        if (!session()->has("$this->uploadSession.chunks")) {
            session()->put("$this->uploadSession.chunks", []);
        }

        return $this->uploadSession;
    }

    private function getSessionChunks(string $filePath)
    {
        if (!$this->uploadSession) {
            $this->uploadSession = $this->getUploadSession($filePath);
        }
        return session()->get("$this->uploadSession.chunks");
    }

    private function setSessionChunks(array $sessionChunks)
    {
        if (!$this->uploadSession) {
            throw new OmenException('upload session is not defined');
        }
        session()->put("$this->uploadSession.chunks", $sessionChunks);
    }

    /**
     * Clears the upload session
     * @return void 
     * @throws OmenException 
     */
    private function clearUploadSession()
    {
        if (!$this->uploadSession) {
            throw new OmenException('upload session is not defined');
        }

        //* Clear Session upload *//
        session()->put($this->uploadSession, null);
    }

    private function assembleUploadedFile(string $fileName, string $filePath, int $fileSize)
    {
        $this->clearUploadSession();

        //* Assemble all chunks in private path *//
        $outFile = $this->fm->inode(OmenHelper::privatePath(sprintf(
            'out/%s%s',
            \uniqid(),
            $fileName
        )));
        $outFileSize = 0;

        /**
         ** check if file exists and delete before building it
         ** This should not happen since file name is random
         */
        if ($this->fm->exists($outFile)) {
            try {
                $outFile->delete();
            } catch (OmenException $e) {
                \report($e);
                try {
                    $this->clearChunks();
                } finally {
                    return $this->errorResponse();
                }
            }
        }

        //* Assemble file frow chunks *//
        try {
            foreach ($this->chunkFiles as $chunkFilePath) {
                $chunkInode = $this->fm->inode($chunkFilePath);
                $d = $chunkInode->get();
                $outFileSize += \strlen($d);
                $outFile->append($d);
                unset($d);
                $chunkInode->delete();
            }
        } catch (ExceptionFileNotFoundException $e) {
            $exception = new OmenException('File manipulation error', '87' . __LINE__, $e);
            \report($exception);
            return $this->cleanAndGetErrorResponse($outFile, $this->chunkIndex);
        }

        //* Check assembled file real size *//
        if ($outFileSize != $fileSize) {
            return $this->cleanAndGetErrorResponse($outFile, $this->chunkIndex, 400, __('Uploaded file was truncated, missing data'));
        }

        //* Check file size is not too large *//
        if (
            config('omen.maxUploadSize') and
            $outFileSize > \intval(\rtrim(config('omen.maxUploadSize'), 'M'))
        ) {
            return $this->cleanAndGetErrorResponse($outFile, $this->chunkIndex, 400, __('Uploaded file is too large'));
        }

        //* Check the final assembled file mimeType *//
        $mimeTypeChecker = new MimeTypes();
        $mimeType = null;
        try {
            if ($mimeTypeChecker->isGuesserSupported()) {
                $mimeType = $mimeTypeChecker->guessMimeType(storage_path(\sprintf('app%s%s', \DIRECTORY_SEPARATOR, $outFile->getFullPath())));
                if (!\count(\array_intersect($mimeTypeChecker->getExtensions($mimeType), OmenHelper::getAllowedFilesExtensions()))) {
                    return response()->json(['error' => __('File type not allowed')], 400);
                }
            }
        } catch (Exception $e) {
            $exception = new OmenException('Could not check file extension', '88' . __LINE__, $e);
            \report($exception);
            return $this->cleanAndGetErrorResponse($outFile, $this->chunkIndex);
        }

        //* Finally save the uploaded File to the upload disk *//
        $this->fm->switchToDisk(Disk::PUBLIC);
        try {
            $uploadedInode = $this->fm->inode(OmenHelper::uploadPath($filePath));
            $uploadedInode->put($outFile->get());
            $outFile->delete();
            session()->save();
            return response()->json([
                'chunkIndex' => $this->chunkIndex,
                'filename' => $fileName,
                'inode' => $uploadedInode
            ]);
        } catch (OmenException $e) {
            report($e);
            return $this->errorResponse();
        } catch (ExceptionFileNotFoundException $e) {
            $exception = new OmenException('File manipulation error', '87' . __LINE__, $e);
            \report($exception);
            return $this->errorResponse();
        }
    }
}
