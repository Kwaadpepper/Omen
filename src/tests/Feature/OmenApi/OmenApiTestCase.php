<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Testing\TestResponse as TestingTestResponse;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\Lib\CSRF;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;
use LogicException;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class OmenApiTestCase extends TestCase
{
    private static $omenCSRFToken = null;
    private static $CSRFCOOKIE = null;

    protected function getPackageProviders($app)
    {
        return [
            'Kwaadpepper\Omen\Providers\OmenServiceProvider',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        \config(['app.debug' => true]);
        $response = $this->omenApiRequest('GET', route('omenInterface', [], false));
        static::$CSRFCOOKIE = \urldecode(\explode('=', \explode(';', $response->headers->get('set-cookie'))[0])[1]);
    }

    /**
     * Download file
     * @param mixed $sourcePath 
     * @param bool $inline 
     * @return TestResponse|TestingTestResponse
     */
    protected function omenApiDownload(string $sourcePath, bool $inline = false)
    {
        $opts = ['file' => $sourcePath];
        if ($inline) {
            $opts['view'] = true;
        }
        return $this->omenApiRequest('GET', route('httpFileSend.omenDownload', $opts, false));
    }

    /**
     * 
     * @param string $inodepath 
     * @param string $inodename
     */
    protected function omenApiRename(string $inodepath, string $inodename)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenRename', [], false), [
            'inodepath' => $inodepath,
            'inodename' => $inodename
        ]);
    }

    protected function omenApiDelete(string $inodepath)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenDelete', [], false), [
            'inodepath' => $inodepath
        ]);
    }

    /**
     * 
     * @param string $sourcePath 
     * @param string $destPath
     */
    protected function omenApiMoveTo(string $sourcePath, string $destPath)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenMoveto', [], false), [
            'sourcePath' => $sourcePath,
            'destPath' => $destPath
        ]);
    }
    /**
     * 
     * @param string $sourcePath 
     * @param string $destPath
     */
    protected function omenApiCopyTo(string $sourcePath, string $destPath)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenCopyto', [], false), [
            'sourcePath' => $sourcePath,
            'destPath' => $destPath
        ]);
    }

    /**
     * 
     * @param string $directoryPath 
     * @param string $directoryName
     */
    protected function omenApiCreateDirectory(string $directoryPath, string $directoryName)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenCreateDirectory', [], false), [
            'directoryPath' => $directoryPath,
            'directoryName' => $directoryName
        ]);
    }

    /**
     * 
     * @param string $filepath 
     * @param float $fileheight 
     * @param float $filewidth 
     * @return TestResponse|TestingTestResponse
     */
    protected function omenApiResize(string $filepath, $fileheight, $filewidth)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenResizeImage', [], false), [
            'filepath' => $filepath,
            'fileheight' => $fileheight,
            'filewidth' => $filewidth
        ]);
    }

    protected function omenApiCrop(string $filepath, $x, $y, $width, $height, $rotate = 0, $scaleX = 0, $scaleY = 0)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenCropImage', [], false), [
            'filepath' => $filepath,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'rotate' => $rotate,
            'scaleX' => $scaleX,
            'scaleY' => $scaleY
        ]);
    }

    protected function omenApiGetInode(string $inodepath)
    {
        return $this->omenApiRequest('GET', route('OmenApi.omenGetInode', [], false), [
            'inodepath' => $inodepath
        ]);
    }

    protected function omenApiGetInodes(string $path)
    {
        return $this->omenApiRequest('GET', route('OmenApi.omenGetInodes', [], false), [
            'path' => $path
        ]);
    }

    protected function omenApiGetBreadcrumb(string $path)
    {
        return $this->omenApiRequest('GET', route('OmenApi.omenGetBreadcrumb', [], false), [
            'path' => $path
        ]);
    }

    protected function omenApiPing()
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenPing', [], false), []);
    }

    protected function omenApiLog(int $code, string $message)
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenLog', [], false), [
            'code' => $code,
            'message' => $message
        ]);
    }

    protected function omenApiCspReport(string $message)
    {
        $cspReport = [
            'csp-report' => [
                'violated-directive' => $message,
                'document-uri' => '',
                'blocked-uri' => '',
                'original-policy' => '',
                'referrer' => '',
            ]
        ];
        $response = $this->call('POST', route('OmenReport.omenCspReport', [], false), [], [
            'XSRF-TOKEN' => static::$CSRFCOOKIE
        ], [], [
            sprintf('HTTP_%s', CSRF::getHeaderName()) => static::$omenCSRFToken,
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ], json_encode($cspReport));
        static::$omenCSRFToken = session()->get('omenCSRFToken');
        $exception = $response->baseResponse->exception ?? null;
        $this->assertNull($exception, $exception ?? '');
        return $response;
    }

    protected function omenApiCreateTextFile(string $filePath, string $fileName, string $fileText = 'content')
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenCreateTextFile', [], false), [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'fileText' => $fileText
        ]);
    }

    protected function omenApiUpdateTextFile(string $filePath, string $fileText = 'content')
    {
        return $this->omenApiRequest('POST', route('OmenApi.omenUpdateTextFile', [], false), [
            'filePath' => $filePath,
            'fileText' => $fileText
        ]);
    }

    protected function omenApiUpload(
        string $realFilePath,
        string $uploadFilePath,
        string $fileName,
        int $fileSize,
        string $fileId,
        int $chunkIndex,
        int $chunkCount,
        string $content
    ) {
        File::put(sprintf('%s/%s', $realFilePath, $fileName), $content);
        $uploadedFile = new UploadedFile(
            sprintf('%s/%s', $realFilePath, $fileName),
            $fileName,
            null,
            null,
            true,
        );
        $response =  $this->call('POST', route('OmenApi.omenUpload', [], false), [
            'filePath' => $uploadFilePath,
            'fileName' => $fileName,
            'fileSize' => $fileSize,
            'fileId' => $fileId,
            'chunkIndex' => $chunkIndex,
            'chunkCount' => $chunkCount
        ], [
            'XSRF-TOKEN' => static::$CSRFCOOKIE
        ], ['fileBlob' => $uploadedFile], [
            sprintf('HTTP_%s', CSRF::getHeaderName()) => static::$omenCSRFToken
        ]);
        File::delete(sprintf('%s/%s', $realFilePath, $fileName));
        static::$omenCSRFToken = session()->get('omenCSRFToken');
        $exception = $response->baseResponse->exception ?? null;
        $this->assertNull($exception, $exception ?? '');
        return $response;
    }

    protected function getFileChunks(int $chunkCount = 1)
    {
        $chunks = [];
        $totalSize = 0;
        $chunkDefinedSize = 2; // 2MB

        for ($i = 0; $i < $chunkCount; $i++) {
            list($chunkContent, $chunkSize) = $this->genUploadFileContent(
                $chunkCount * $chunkDefinedSize
            );
            $totalSize .= $chunkSize;
            \array_push($chunks, [
                'content' => $chunkContent,
                'size' => $chunkSize
            ]);
        }
        return [
            $totalSize,
            $chunks
        ];
    }

    /**
     * @param string $filename 
     * @param float $fileSize 
     * @return (string|int)[] 
     */
    protected function genUploadFileContent(float $fileSize = 1.5)
    {
        $content = str_repeat('A', 1024 * 1024 * $fileSize);
        return [$content, \strlen($content)];
    }

    /**
     * Call a request to omenApi
     * @param string $method 
     * @param string $url 
     * @param array $parameters 
     */
    protected function omenApiRequest(string $method, string $url, array $parameters = [], array $headers = [])
    {
        $response = $this->call($method, $url, $parameters, [
            'XSRF-TOKEN' => static::$CSRFCOOKIE
        ], [], \array_merge($headers, [
            sprintf('HTTP_%s', CSRF::getHeaderName()) => static::$omenCSRFToken
        ]));
        static::$omenCSRFToken = session()->get('omenCSRFToken');
        $exception = $response->baseResponse->exception ?? null;
        $this->assertNull($exception, $exception ?? '');
        return $response;
    }

    /**
     * Create a directory
     * @param string $dirPath 
     * @return string
     */
    protected function createDirectory(string $dirPath, $private = false)
    {
        $this->removeDirectory($dirPath);
        if (!$private) {
            $path = \storage_path(sprintf('app/%s', OmenHelper::uploadPath($dirPath)));
        } else {
            $path = \storage_path(sprintf('app/%s', OmenHelper::privatePath($dirPath)));
        }
        $message = sprintf('Could not create directory for rename test %s', $path);
        if (!File::exists($path)) {
            $this->assertTrue(File::makeDirectory($path), $message);
        }
        $this->assertDirectoryExists($path, $message);
        return $dirPath;
    }

    /**
     * Remove a directory
     * @param string $dirPath 
     * @return string
     */
    protected function removeDirectory(string $dirPath)
    {
        $path = \storage_path(sprintf('app/%s', OmenHelper::uploadPath($dirPath)));
        $message = sprintf('Could not remove directory for rename test %s', $path);
        File::deleteDirectory($path);
        $this->assertDirectoryDoesNotExist($path, $message);
        return $dirPath;
    }

    /**
     * Create a file
     * @param string $filename 
     * @return string
     */
    protected function createFile(string $filename, $private = false)
    {
        if (!$private) {
            $path = \storage_path(sprintf('app/%s', OmenHelper::uploadPath($filename)));
        } else {
            $path = \storage_path(sprintf('app/%s', OmenHelper::privatePath($filename)));
        }
        File::put($path, 'content');
        $this->assertFileExists($path);
        return $filename;
    }

    /**
     * remove a File
     * @param string $filename 
     * @return void
     */
    protected function removeFile(string $filename)
    {
        $path = \storage_path(sprintf('app/%s', OmenHelper::uploadPath($filename)));
        File::delete($path);
        $this->assertFileDoesNotExist($path);
    }

    private static $image;

    /**
     * Generate a static image
     * @return string
     * @throws OmenException 
     */
    protected function genImage()
    {
        $this->clearImage();
        $filePath = \sprintf('%s/../../resources/image.png', __DIR__);
        $imageContent = File::get($filePath);
        static::$image = (new FileManager())->inode(OmenHelper::uploadPath('image.png'));
        $this->assertTrue(static::$image->put($imageContent), 'Could not generate image content');
        return static::$image->getPath();
    }

    protected function clearImage()
    {
        if (static::$image) {
            static::$image->delete();
        }
        static::$image = null;
    }
}
