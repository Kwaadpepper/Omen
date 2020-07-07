<?php

namespace Kwaadpepper\Omen\Tests\Browser;

use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\OmenHelper;
use Orchestra\Testbench\Dusk\TestCase;

abstract class BrowserTestCase extends TestCase
{
    protected static $baseServeHost = '127.0.0.1';
    protected static $baseServePort = 8000;

    protected function getPackageProviders($app)
    {
        return ['Kwaadpepper\Omen\Providers\OmenServiceProvider'];
    }

    protected function setUp(): void
    {
        // \Orchestra\Testbench\Dusk\Options::withoutUI();
        parent::setUp();
        $this->createDirectory('DirA');
        $this->createDirectory('DirB');
        $this->createFile('fileA.txt');
        $this->createFile('fileB.txt');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
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
