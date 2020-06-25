<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Exception;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\LocalFile;
use Kwaadpepper\Omen\OmenHelper;
use Orchestra\Testbench\TestCase;

class LocalFileTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'Kwaadpepper\Omen\Providers\OmenServiceProvider',
        ];
    }

    /** @test */
    public function test()
    {
        try {
            $inode = (new FileManager())->inode(OmenHelper::privatePath('file.txt'));
            $inode->put('hello');
            $localFile = new LocalFile($inode);
            $localFile->getMimeType();
            $fpath = $localFile->getRealPath();
            $this->assertFileExists($fpath);
            $this->assertEquals('hello', \file_get_contents($fpath));
            $localFile->release();
            $this->assertFileDoesNotExist($fpath);
        } catch (Exception $e) {
            $this->fail(sprintf('LocalFile %s', $e));
        }
    }
}
