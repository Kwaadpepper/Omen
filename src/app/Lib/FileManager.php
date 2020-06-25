<?php

namespace Kwaadpepper\Omen\Lib;

use Error;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\OmenHelper;
use LogicException as GlobalLogicException;
use Symfony\Component\Mime\Exception\LogicException;
use Symfony\Component\Mime\MimeTypes;

class FileManager
{
    // for thumbs usage
    static $privateDisk;

    // The user configurated one
    static $publicDisk;

    // The selected disk
    static $currentDisk;

    public function __construct()
    {
        static::$currentDisk = Disk::PUBLIC;
        static::$publicDisk = Storage::disk(config('omen.publicDisk'));
        static::$privateDisk = Storage::disk(config('omen.privateDisk'));


        // Check if public and private path Exists on Disks
        if (!$this->exists(config('omen.publicPath'))) {

            // then create the folder path
            $this->createDirectory(config('omen.publicPath'));
        }

        // Check if private path Exists on Disks
        if (!$this->exists(config('omen.privatePath'))) {
            $this->switchToDisk(Disk::PRIVATE);
            // then create the folder path
            $this->createDirectory(config('omen.privatePath'));
            $this->switchToDisk(Disk::PUBLIC);
        }
    }

    /**
     * Changes the current selected disk
     * @param Disk $disk The disk type to use
     * @return void 
     */
    public function switchToDisk($disk)
    {
        self::$currentDisk = $disk;
    }

    /**
     * Get all Children inodes info (Dirs and Files)
     * @param String|Inode $pathOrInode the inode/path to test
     * @return Array[Inodes] an array of Inodes
     */
    public function inodes($pathOrInode)
    {
        $inodes = [];
        $disk = $this->getDisk();

        if (self::isInodeType($pathOrInode)) {
            $pathOrInode = $pathOrInode->getFullPath();
        }

        foreach ($disk->directories($pathOrInode) as $directoryFullPath) {
            $i = new Inode($directoryFullPath, InodeType::DIR, $disk);
            $inodes[\base64_encode($i->getPath())] = $i;
        }

        foreach ($disk->files($pathOrInode) as $fileFullPath) {
            $i = new Inode($fileFullPath, InodeType::FILE, $disk);
            $inodes[\base64_encode($i->getPath())] = $i;
        }

        return $inodes;
    }

    /**
     * Find files in a directory matching a filename pattern
     * @param String $dirPath 
     * @param Regex $fileNamePattern 
     * @param String|null $flags 
     * @return Array[String] 
     */
    public function globFiles($dirPath, $fileNamePattern = '', $flags = null)
    {
        $outputMatch = [];
        $disk = $this->getDisk();

        foreach ($disk->files($dirPath) as $filePath) {
            if (\preg_match("/$fileNamePattern/", $filePath, $match, $flags)) {
                $outputMatch[] = $filePath;
            }
        }

        return $outputMatch;
    }

    /**
     * Get an inode
     * @param String $path
     * @return Inode
     */
    public function inode($path)
    {
        $disk = $this->getDisk();

        return new Inode($path, '', $disk);
    }

    /**
     * Test the existence of an inode
     * @param String|Inode $pathOrInode the inode/path to test
     * @return Bool the result
     */
    public function exists($pathOrInode)
    {
        $disk = $this->getDisk();

        if (self::isInodeType($pathOrInode)) {
            $pathOrInode = $pathOrInode->getFullPath();
        }

        return $disk->exists($pathOrInode);
    }


    /**
     * Move an inode to another
     * @param string|Inode $sourcePathOrInode 
     * @param string|Inode $destPathOrInode 
     * @return Inode|void 
     * @throws OmenException 
     */
    public function moveTo($sourcePathOrInode, $destPathOrInode)
    {
        $disk = $this->getDisk();

        $fm = new FileManager();
        if ($fm::isInodeType($sourcePathOrInode)) {
            $sourcePathOrInode = $sourcePathOrInode->getFullPath();
        }

        if ($fm::isInodeType($destPathOrInode)) {
            $destPathOrInode = $destPathOrInode->getFullPath();
        }


        $sourcePathOrInode = OmenHelper::sanitizePath($sourcePathOrInode);
        $destPathOrInode = OmenHelper::sanitizePath($destPathOrInode);

        $sourceInode = $fm->inode($sourcePathOrInode);
        $destInode = $fm->inode($destPathOrInode);

        if ($destInode->getType() == InodeType::DIR) {
            $destInode = $fm->inode(sprintf('%s/%s', $destInode->getDir(), $sourceInode->getFullName()));
        }

        try {
            if (
                $destInode->getType() == InodeType::FILE &&
                $fm->exists($destInode) &&
                config('omen.overwriteOnFileMove')
            ) {
                $destInode->delete();
            }
            if (!$disk->move($sourceInode->getFullPath(), $destInode->getFullPath())) {
                throw new OmenException(
                    \sprintf('File move failed from %s to %s', $sourceInode->getFullPath(), $destInode->getFullPath())
                );
            }
            return $destInode;
        } catch (Exception $e) {
            throw new OmenException(
                \sprintf('File move failed from %s to %s', $sourceInode->getFullPath(), $destInode->getFullPath()),
                $e
            );
        }
    }

    /**
     * Copy an inode to another
     * @param string|Inode $sourcePathOrInode 
     * @param string|Inode $destPathOrInode 
     * @return Inode|void 
     * @throws OmenException 
     */
    public function copyTo($sourcePathOrInode, $destPathOrInode)
    {
        $disk = $this->getDisk();

        $fm = new FileManager();
        if ($fm::isInodeType($sourcePathOrInode)) {
            $sourcePathOrInode = $sourcePathOrInode->getFullPath();
        }

        if ($fm::isInodeType($destPathOrInode)) {
            $destPathOrInode = $destPathOrInode->getFullPath();
        }


        $sourcePathOrInode = OmenHelper::sanitizePath($sourcePathOrInode);
        $destPathOrInode = OmenHelper::sanitizePath($destPathOrInode);

        $sourceInode = $fm->inode($sourcePathOrInode);
        $destInode = $fm->inode($destPathOrInode);

        if ($destInode->getType() == InodeType::DIR) {
            $destInode = $fm->inode(sprintf('%s/%s', $destInode->getDir(), $sourceInode->getFullName()));
        }

        try {
            if (
                $destInode->getType() == InodeType::FILE &&
                $fm->exists($destInode) &&
                config('omen.overwriteOnFileCopy')
            ) {
                $destInode->delete();
            }
            if (!$disk->copy($sourceInode->getFullPath(), $destInode->getFullPath())) {
                throw new OmenException(
                    \sprintf('File copy failed from %s to %s', $sourcePathOrInode, $destPathOrInode)
                );
            }
            return $destInode;
        } catch (Exception $e) {
            throw new OmenException(
                \sprintf('File copy failed from %s to %s', $sourceInode->getFullPath(), $destInode->getFullPath()),
                $e
            );
        }
    }

    public function getNewFileName($path, $counter = 0)
    {
        $extension = OmenHelper::mb_pathinfo($path, \PATHINFO_EXTENSION);
        $basePath = OmenHelper::mb_rtrim($path, ".$extension");

        // Try give another filename if the one wanted already exists on storage
        while (++$counter < 30) {
            $newPath = sprintf('%s%d.%s', $basePath, $counter, $extension);
            if (!$this->exists($newPath)) {
                return $newPath;
            }
        }
        // if this failed omg, give a random filename
        try {
            // try this if openSSL is installed
            return sprintf('%s%d.%s', $path, Str::random(), $extension);
        } catch (Error $e) {
        } finally {
            return sprintf('%s%d.%s', $path, \base64_encode(time()), $extension);
        }
    }

    /**
     * Creates a Directory recursively
     * @param String $path the directory path to create
     * @return void 
     * @throws OmenException 
     */
    public function createDirectory(string &$path)
    {
        // Check if parent Exists
        $dirName = OmenHelper::mb_pathinfo($path, \PATHINFO_DIRNAME);
        if ($dirName != '' and !$this->exists($dirName)) {
            // Sanitize parent name to create
            $cp = OmenHelper::mb_pathinfo($dirName, \PATHINFO_BASENAME);
            $cp = \str_replace($cp, OmenHelper::filterFilename($cp), $dirName);
            $path = \str_replace($dirName, $cp, $path);
            $dirName = $cp;
            // Create the parent
            $this->createDirectory($dirName);
        }

        $error = null;

        \set_error_handler(function ($code, $message, $filePath, $line, $context) use (&$error) {
            $error = new \League\Flysystem\Exception(\sprintf('%s in %s line %d', $message, $filePath, $line), $code);
        });

        $disk = $this->getDisk();
        if (!$disk->makeDirectory($path)) {
            \restore_error_handler();
            throw new OmenException(\sprintf('Folder %s could not be created', config('omen.privatePath')), $error);
        }
        \restore_error_handler();

        $disk->setVisibility($path, 'private');
    }

    /**
     * Check if the file extension is matching its mimetype
     * @param string $inodeFullPath 
     * @return bool 
     * @throws OmenException 
     * @throws LogicException 
     */
    public function checkExtensionWithMimeType(string $inodeFullPath)
    {
        try {
            $ext = OmenHelper::mb_pathinfo($inodeFullPath, \PATHINFO_EXTENSION);
            $exts = $this->getFilePossibleExtensions($inodeFullPath);
            if (\in_array($ext, $exts)) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new OmenException('Could not check file extension', $e);
        }
    }

    /**
     * Check if the file is allowed by Omen config
     * @param string $inodeFullPath 
     * @return true 
     * @throws OmenException If mime type could not be checked
     */
    public function isAllowedMimeType(string $inodeFullPath)
    {
        try {
            $exts = $this->getFilePossibleExtensions($inodeFullPath);
            $ext = OmenHelper::mb_pathinfo($inodeFullPath, \PATHINFO_EXTENSION);
            if (\count($exts) > 0 && \in_array($ext, OmenHelper::getAllowedFilesExtensions())) {
                return true;
            }
            return false;
        } catch (GlobalLogicException | GlobalLogicException | LogicException $e) {
            throw new OmenException('Could not check file mime type', $e);
        }
    }

    /**
     * Get the file possible extensions
     * @param string $inodeFullPath 
     * @return string[] — an array of extensions (first one is the preferred one)
     * @throws GlobalLogicException 
     * @throws InvalidArgumentException 
     * @throws LogicException
     * @throws OmenException
     */
    public function getFilePossibleExtensions(string $inodeFullPath)
    {
        $ext = OmenHelper::mb_pathinfo($inodeFullPath, \PATHINFO_EXTENSION);
        $mt = new MimeTypes();
        $exts = $mt->getExtensions($mt->guessMimeType($inodeFullPath));
        if (!\in_array($ext, $exts)) {
            throw new OmenException(\sprintf(
                'File guessed extensions %s does not match file name extension %s',
                \implode(',', $exts),
                $ext
            ));
        }
        return $exts;
    }

    /**
     * returns the current selected disk
     * @return Filesystem the current disk
     */
    public function getDisk()
    {
        if (self::$currentDisk == Disk::PUBLIC) return self::$publicDisk;
        else return self::$privateDisk;
    }

    /**
     * Test if element is an Inode Object
     * @param mixed $element the lement to tet
     * @return bool true if is type of Kwaadpepper\Omen\Lib\Inode
     */
    private static function isInodeType($element)
    {
        if (\gettype($element) == 'object' and \get_class($element) === Inode::class) {
            return true;
        }
        return false;
    }
}
