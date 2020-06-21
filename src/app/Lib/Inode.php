<?php

namespace Kwaadpepper\Omen\Lib;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;
use JsonSerializable;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\OmenHelper;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypes;

class Inode implements JsonSerializable
{
    private $disk = null;
    private $fullPath = null;
    private $path = null;
    private $dirName = null;
    private $baseName = null;
    private $type = null;
    private $fileType = null;
    private $mimeType = null;
    private $size = null;
    private $lastModified = null;
    private $visibility = null;
    private $url = null;

    /**
     * 
     * @param string $path 
     * @param InodeType $type 
     * @return void 
     */
    public function __construct(string $fullPath, string $type, Filesystem $disk)
    {
        $this->disk = $disk;

        if (empty($type)) {
            $this->type = $this->isPathDirectory($fullPath, $disk) ? InodeType::DIR : InodeType::FILE;
        } else {
            $this->type = $type;
        }

        $this->initWithFullPath($fullPath);

        if ($this->mimeType = $this->getMimeTypeFromFileName()) {
            $this->fileType = InodeFileType::getFromMimeType($this->mimeType);
        } elseif ($this->type == InodeType::FILE) {
            $this->fileType = InodeFileType::getFromMimeType('application/octet-stream');
            $this->mimeType = 'application/octet-stream';
        }
    }

    private function initWithFullPath($fullPath)
    {
        $pathPrefix = config('omen.publicPath');

        // remove path Prefix
        if (\substr($fullPath, 0, \strlen($pathPrefix)) == $pathPrefix) {
            $this->path = \substr($fullPath, \strlen($pathPrefix));
        } else {
            $this->path = $fullPath;
        }

        $this->fullPath = $fullPath;
        if ($this->disk->exists($this->fullPath)) {
            $this->size = $this->disk->size($this->fullPath);
            $this->visibility = $this->disk->getVisibility($this->fullPath);
            $this->lastModified = $this->disk->lastModified($this->fullPath);
        }
        $this->dirName = OmenHelper::mb_pathinfo($fullPath, \PATHINFO_DIRNAME);
        $this->baseName = OmenHelper::mb_pathinfo($fullPath, \PATHINFO_BASENAME);
        $this->baseName = empty($this->baseName) ? '/' : $this->baseName;

        try {
            if ($this->type == InodeType::FILE) {
                $this->url = $this->disk->url($fullPath);

                // check if is a HTTP url
                if (empty(\parse_url($this->url, \PHP_URL_HOST))) {
                    // fallback to omen serve file
                    $this->url = Application::getInstance()->make('url')->route('httpFileSend.omenDownload', \ltrim($this->path, '/'));
                }
            }
        } catch (RuntimeException $e) {
            \report(new OmenException(sprintf('Can\'t get this inode public url %s ', $fullPath), $e));
            $this->url = Application::getInstance()->make('url')->route('httpFileSend.omenDownload', \ltrim($this->path, '/'));
        }
    }

    /**
     * Get the inode content
     * This is not currently supported on a directory
     * @return String    
     * @throws OmenException  if inode is a directory
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get()
    {
        if ($this->type == InodeType::DIR) {
            throw new OmenException('Calling method get on a Directory is not supported');
        }
        return $this->disk->get($this->fullPath);
    }

    /**
     * Get the inode data within a temp file on the local disk
     * @return Kwaadpepper\Omen\Lib\LocalFile|False A new temp file with the inode data | false is an error occurs
     * @throws Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException if the inode got not be retrieved
     */
    public function getToTempLocalFile()
    {
        try {
            return new LocalFile($this);
        } catch (OmenException $e) {
            return false;
        } catch (FileNotFoundException $e) {
            // this should not happen ever
            return false;
        }
    }

    /**
     * Set the inode content
     * This is can't be done on a directory
     * @param Mixed $content
     * @return Boolean    
     * @throws OmenException  if inode is a directory
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException if temp file could not be created
     */
    public function put($content)
    {
        if ($this->type == InodeType::DIR) {
            throw new OmenException('Calling method get on a Directory is not supported');
        }

        $filename = \tempnam(\sys_get_temp_dir(), '');
        \file_put_contents($filename, $content);

        $file = new File($filename);

        $out = $this->disk->putFileAs($this->dirName, $file, $this->baseName, $this->getVisibility()) ? true : false;

        if (function_exists('mb_strlen')) {
            $this->size = \mb_strlen($content, '8bit');
        } else {
            $this->size = \strlen($content);
        }

        $this->lastModified = \time();

        return $out;
    }

    /**
     * Append content to the inode
     * This is can't be done on a directory
     * 
     *? This works only for local storage with File Adapter ?
     * 
     * @param Mixed $content
     * @return Boolean    
     * @throws OmenException  if inode is a directory
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException if file could not be found
     */
    public function append($content)
    {
        if ($this->type == InodeType::DIR) {
            throw new OmenException('Calling method append on a Directory is not supported');
        }

        $this->lastModified = \time();

        /**
         * Because the FileSystem contract does not specify the third parameter on
         * append method, we have to check manually
         * if the file system is Local, if so we have to prevent it
         * to append PHP_EOL before $content so it won't break binary files
         * or alter any file content by the way
         * 
         * Test if driver is Filesystem
         * is sequence, we can't be sure of the driver and the adapter
         * so we need to test first the disk type, then the driver type
         * and finally the adpater type to be sure its local
         * 
         * Another work around would be to check the storage type of
         * the FileManager instance, witch is not accessible from this object tough.
         */
        if (
            get_class($this->disk) == 'Illuminate\Filesystem\FilesystemAdapter'
            and  \get_class($this->disk->getDriver()) == 'League\Flysystem\Filesystem'
            and get_class($this->disk->getDriver()->getAdapter()) == 'League\Flysystem\Adapter\Local'
        ) {
            // prevent append of PHP_EOL before content
            return $this->disk->append($this->fullPath, $content, null);
        } else {
            return $this->disk->append($this->fullPath, $content);
        }
    }

    /**
     * Create a streamed response for a given file.
     *
     * @param  String|Null  $name
     * @param  array|null  $headers
     * @param  string|null  $disposition
     * @throws OmenException if inode is not a File
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function response($name = null, array $headers = [], $disposition = 'inline')
    {
        $disk = $this->disk;
        $fullPath = $this->fullPath;

        if ($this->type == InodeType::DIR) {
            throw new OmenException('Calling method get on a Directory is not supported');
        }

        $response = new StreamedResponse();

        $filename = $name ?? $this->baseName;

        $disposition = $response->headers->makeDisposition(
            $disposition,
            $filename,
            $this->fallbackName($filename)
        );

        $response->headers->replace($headers + [
            'Content-Type' => $this->mimeType,
            'Content-Length' => $this->size,
            'Content-Disposition' => $disposition,
        ]);

        $response->setCallback(function () use ($disk, $fullPath) {
            $stream = $disk->readStream($fullPath);
            \fpassthru($stream);
            \fclose($stream);
        });

        return $response;
    }

    /**
     * Create a streamed download response for a given file.
     *
     * @param  String|Null  $name
     * @param  Array|Null  $headers
     * @throws OmenException if inode is not a File
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($name = null, array $headers = [])
    {
        return $this->response($name, $headers, 'attachment');
    }

    /**
     * Remove this inode from Storage
     * @throws OmenException if file delete error
     * @return void 
     */
    public function delete()
    {
        if (!$this->disk->delete($this->fullPath)) {
            throw new OmenException(sprintf('Could not delete file %s', $this->fullPath));
        }
    }

    public function getLastModfied()
    {
        return $this->lastModified;
    }

    /**
     * Get the inode url
     * @return String 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets the directory of this inode
     * @return String
     */
    public function getDir()
    {
        return $this->dirName;
    }

    /**
     * Gets the type of inode
     * @return InodeType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the size of inode
     * @return Integer
     */
    public function getSize()
    {
        return OmenHelper::HumanReadableBytes($this->size);
    }

    /**
     * Gets the type of inode
     * @return InodeFileType
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    public function getDateFormated()
    {
        return Date::parse($this->lastModified)->isoFormat('dddL');
    }

    /**
     * Gets the Inode Folder
     * @return String 
     */
    public function getFolder()
    {
        return $this->type != InodeType::DIR ?
            \dirname($this->fullPath) : $this->fullPath;
    }

    /**
     * Gets the Inode path
     * @return String the full path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the full path of the Inode
     * @return String the full path
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * Gets the inode full name
     * with extension if is a file
     * @return String 
     */
    public function getFullName()
    {
        return $this->baseName;
    }

    /**
     * Gets the inode name
     * without extension if is a file
     * @return String 
     */
    public function getName()
    {
        return $this->type != InodeType::DIR ?
            OmenHelper::mb_pathinfo($this->baseName, \PATHINFO_FILENAME) : $this->baseName;
    }

    /**
     * Change the inode base name
     * @param String $name 
     * @return void 
     */
    public function setFullName($name)
    {
        $newFullPath = sprintf('%s/%s', $this->getFolder(), $name);
        $oldFulPath = $this->fullPath;

        # move on storage to rename
        $this->disk->move($oldFulPath, $newFullPath);

        # update inode variables
        $this->initWithFullPath($newFullPath);
    }

    /**
     * Get the inode visibility
     * @return String 
     */
    public function getVisibility()
    {
        if (\is_null($this->visibility)) {
            $this->setVisibilty(config('omen.defaultVisibility'));
        }
        return $this->visibility;
    }

    /**
     * set the inode visibility
     * @return void 
     * @throws OmenException if $visibility is not of type InodeVisibility
     */
    public function setVisibilty(string $visibility)
    {
        switch ($visibility) {
            case InodeVisibility::PRIVATE:
            case InodeVisibility::PUBLIC:
                $this->visibility = $visibility;
                break;
            default:
                throw new OmenException(sprintf('%s must type of InodeVisibility', $visibility));
        }
    }

    /**
     * Get the inode file extension
     * returns false if is a folder
     * @return String|Boolean 
     */
    public function getExtension()
    {
        return $this->type != InodeType::DIR ?
            OmenHelper::mb_pathinfo($this->baseName, \PATHINFO_EXTENSION) : false;
    }

    /**
     * Get the Inode Mimetype
     * @return String|Boolean Possible Mimetypes or false if is a directory or can't guess the mimeType
     */
    public function getMimeTypeFromFileName()
    {
        return $this->getPossibleMimeTypesFromFileName()[0] ?? false;
    }

    /**
     * Get the Inode Mimetype
     * @return Array|Boolean Possible Mimetypes or false if is a directory or can't guess the mimeType
     */
    public function getPossibleMimeTypesFromFileName()
    {
        return $this->type != InodeType::DIR ?
            (new MimeTypes())->getMimeTypes($this->getExtension()) ?? false : false;
    }

    /**
     * Serializable contract
     * @return Array Object representation
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'path' => $this->path,
            'dirName' => $this->dirName,
            'baseName' => $this->baseName,
            'path' => $this->getPath(),
            'url' => $this->url,
            'type' => $this->type,
            'extension' => $this->getExtension(),
            'fileType' => $this->fileType,
            'mimeType' => $this->mimeType,
            'size' => $this->size,
            'lastModified' => $this->lastModified,
            'visibility' => $this->getVisibility()
        ];
    }

    /**
     * Returns a json Object of this inode
     * @return JSON inode
     */
    public function __toString()
    {
        return \json_encode($this->jsonSerialize(), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * Check with Storage if and path is a Directory
     * @param String $path 
     * @return Boolean 
     */
    private function isPathDirectory(string $path, Filesystem $disk)
    {
        // Get parent folder
        $ex = \explode('/', $path);
        $inode = \array_pop($ex);
        $parent = \implode('/', $ex);

        $parentDirectories = $disk->directories($parent);

        return \in_array($path, $parentDirectories);
    }

    /**
     * Convert the string to ASCII characters that are equivalent to the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function fallbackName($name)
    {
        return \str_replace('%', '', Str::ascii($name));
    }
}
