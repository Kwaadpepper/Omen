<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\CrudController;

use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class CopyToTest extends OmenApiTestCase
{
    /** @test */
    public function assertCopyDirectoryFail()
    {
        $sourcePath = 'subfolder';
        $destPath = 'directory';
        $this->createDirectory($destPath);
        $this->createDirectory($sourcePath);
        $response = $this->copyTo($sourcePath, $destPath);
        $response->assertStatus(400);
        $this->removeDirectory($sourcePath);
        $this->removeDirectory($destPath);
    }

    /** @test */
    public function assertCopyFileSuccess()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $response = $this->copyTo($this->createFile('directory/file.txt'), $this->createDirectory('directory2'));
        $response->assertOK();
        $response->assertJsonStructure([
            'name',
            'path',
            'dirName',
            'baseName',
            'url',
            'type',
            'extension',
            'fileType',
            'mimeType',
            'size',
            'lastModified',
            'visibility',
        ]);
        $response->assertJsonFragment([
            'name' => 'file',
            'path' => '/directory2/file.txt',
            'dirName' => OmenHelper::uploadPath('directory2'),
            'baseName' => 'file.txt',
            'type' => InodeType::FILE,
        ]);
        $this->removeDirectory('directory');
        $this->removeDirectory('directory2');
    }

    /** @test */
    public function assertCopyFileFailOnNonExistentFile()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $response = $this->copyTo('directory/fileDontExist.txt', $this->createDirectory('directory2'));
        $response->assertStatus(404);
        $this->removeDirectory('directory');
        $this->removeDirectory('directory2');
    }

    /** @test */
    public function assertCopyFileFailOnCopyDirectory()
    {
        $this->createDirectory('directory');
        $response = $this->copyTo('directory', $this->createDirectory('directory2'));
        $response->assertStatus(400);
        $this->removeDirectory('directory');
        $this->removeDirectory('directory2');
    }

    /** @test */
    public function assertCopyFileFailOnCopyToFileInsteadOFDirectory()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $this->createFile('directory/file2.txt');
        $response = $this->copyTo('directory/file.txt', 'directory/file2.txt');
        $response->assertStatus(400);
        $this->removeDirectory('directory');
    }

    private function copyTo($sourcePath, $destPath)
    {
        return $this->omenApiCopyTo($sourcePath, $destPath);
    }
}
