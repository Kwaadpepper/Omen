<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\CrudController;

use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class MoveToTest extends OmenApiTestCase
{
    /** @test */
    public function assertMoveDirectorySuccess()
    {
        $sourcePath = 'subfolder';
        $destPath = 'directory';
        $this->createDirectory($destPath);
        $this->createDirectory($sourcePath);
        $response = $this->moveTo($sourcePath, $destPath);
        $response->assertStatus(200);
        $this->removeDirectory($sourcePath);
        $this->removeDirectory($destPath);
    }

    /** @test */
    public function assertMoveFileSuccess()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $response = $this->moveTo($this->createFile('directory/file.txt'), $this->createDirectory('directory2'));
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
    public function assertMoveFileFailOnNonExistentFile()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $response = $this->moveTo('directory/fileDontExist.txt', $this->createDirectory('directory2'));
        $response->assertStatus(404);
        $this->removeDirectory('directory');
        $this->removeDirectory('directory2');
    }

    /** @test */
    public function assertMoveFileSuccessOnMoveDirectory()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $response = $this->moveTo('directory', $this->createDirectory('directory2'));
        $response->assertStatus(200);
        $this->removeDirectory('directory');
        $this->removeDirectory('directory2');
    }

    /** @test */
    public function assertMoveFileFailOnMoveToFileInsteadOFDirectory()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $this->createFile('directory/file2.txt');
        $response = $this->moveTo('directory/file.txt', 'directory/file2.txt');
        $response->assertStatus(400);
        $this->removeDirectory('directory');
    }

    private function moveTo($sourcePath, $destPath)
    {
        return $this->omenApiMoveTo($sourcePath, $destPath);
    }
}
