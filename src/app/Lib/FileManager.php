<?php

namespace Kwaadpepper\Omen\Lib;

use Error;
use Illuminate\Support\Str;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\OmenHelper;

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

        foreach ($disk->directories($pathOrInode) as $directoryPath) {
            $inodes[\base64_encode($directoryPath)] = new Inode($directoryPath, InodeType::DIR, $disk);
        }

        foreach ($disk->files($pathOrInode) as $filePath) {
            $inodes[\base64_encode($filePath)] = new Inode($filePath, InodeType::FILE, $disk);
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
    public function globFiles($dirPath, $fileNamePattern, $flags = null)
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
     * @param String|Inode $sourcePathOrInode the source
     * @param String|Inode $destPathOrInode the destination
     * @return void 
     * @throws OmenException If move failed
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

        $filename = OmenHelper::mb_pathinfo($sourcePathOrInode, \PATHINFO_BASENAME);

        if (!$disk->move($sourcePathOrInode, sprintf('%s/%s', $destPathOrInode, $filename))) {
            throw new OmenException(
                \sprintf('File move failed from %s to %s', $sourcePathOrInode, sprintf('%s/%s', $destPathOrInode, $filename)),
                '76' . __LINE__
            );
        }
    }

    /**
     * Copy an inode to another
     * @param String|Inode $sourcePathOrInode the source
     * @param String|Inode $destPathOrInode the destination
     * @return void 
     * @throws OmenException If move failed
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

        $filename = OmenHelper::mb_pathinfo($sourcePathOrInode, \PATHINFO_BASENAME);
        $destPathOrInode = sprintf('%s/%s', $destPathOrInode, $filename);

        if ($this->exists($destPathOrInode)) {
            $destPathOrInode = $this->getNewFileName($destPathOrInode);
        }

        if (!$disk->copy($sourcePathOrInode, $destPathOrInode)) {
            throw new OmenException(
                \sprintf('File copy failed from %s to %s', $sourcePathOrInode, $destPathOrInode),
                '76' . __LINE__
            );
        }
        return $this->inode($destPathOrInode);
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
    public function createDirectory(string $path)
    {
        // Check if parent Exists
        $dirName = OmenHelper::mb_pathinfo($path, \PATHINFO_DIRNAME);
        if ($dirName != '.' and !$this->exists($dirName)) {
            // if not create the parent
            $this->createDirectory($dirName);
        }

        $error = null;

        \set_error_handler(function ($code, $message, $filePath, $line, $context) use (&$error) {
            $error = new \League\Flysystem\Exception(\sprintf('%s in %s line %d', $message, $filePath, $line), $code);
        });

        $disk = $this->getDisk();
        if (!$disk->makeDirectory($path)) {
            \restore_error_handler();
            throw new OmenException(\sprintf('Folder %s could not be created', config('omen.privatePath')), '21' . $error->getLine(), $error);
        }
        \restore_error_handler();

        $disk->setVisibility($path, 'private');
    }

    /**
     * Check the inode MimeType
     * @param string $mimeType 
     * @return void 
     */
    public function isAllowedMimeType($inode)
    {
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
