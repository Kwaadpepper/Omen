<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\CrudController;

use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\Lib\CSRF;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;
use PHPUnit\Framework\ExpectationFailedException;

class DeleteInodeTest extends OmenApiTestCase
{
    /** @test */
    public function deleteDirectorySuccess()
    {
        $this->createDirectory('new');
        $this->assertDeleteDirectory('new');
    }

    /** @test */
    public function deleteDirectoryFailIfDontExists()
    {
        $this->assertDeleteDirectory('ne', false);
    }

    public function deleteDirectoryFailIfNonEmpty()
    {
        // TODO
    }

    /** @test */
    public function renameFile()
    {
        $this->createFile('file.txt');
        $this->assertDeleteFile('file.txt');
        $this->createFile('.new');
        $this->assertDeleteFile('.new');
        $this->createFile('image.jpg');
        $this->assertDeleteFile('image.jpg');
    }

    /** @test */
    public function renameFailFile()
    {
        $this->assertDeleteFile('ne', false);
        $this->assertDeleteFile('ne', false);
        $this->assertDeleteFile('.ne', false);
        $this->assertDeleteFile('.tx', false);
    }

    private function assertDelete(string $inodePath, bool $assertSuccess, string $inodeType)
    {
        $response = $this->omenApiDelete($inodePath);
        if ($assertSuccess) {
            try {
                $response->assertOk();
                $response->assertJsonStructure([]);
                $response->assertJsonFragment([]);
                if ($inodeType == InodeType::DIR) {
                    $this->assertDirectoryDoesNotExist(\storage_path(sprintf('app/%s', OmenHelper::uploadPath($inodePath))));
                } else {
                    $this->assertFileDoesNotExist(\storage_path(sprintf('app/%s', OmenHelper::uploadPath($inodePath))));
                }
            } catch (ExpectationFailedException $e) {
                $this->fail(sprintf('Failed to assert %s has been removed %s', $inodeType, $e));
                $this->removeDirectory($inodePath);
            }
        } else {
            try {
                $this->assertTrue(((array) $response->baseResponse)["\x00*\x00statusCode"] != 200);
                $this->removeDirectory($inodePath);
            } catch (ExpectationFailedException $e) {
                $this->fail(sprintf('Failed to assert %s has not been removed %s', $inodeType, $e));
                $this->removeDirectory($inodePath);
            }
        }
    }

    private function assertDeleteDirectory(string $inodePath, $assertSuccess = true)
    {
        $this->assertDelete($inodePath, $assertSuccess, InodeType::DIR);
    }

    private function assertDeleteFile(string $inodePath, $assertSuccess = true)
    {
        $this->assertDelete($inodePath, $assertSuccess, InodeType::FILE);
    }
}
