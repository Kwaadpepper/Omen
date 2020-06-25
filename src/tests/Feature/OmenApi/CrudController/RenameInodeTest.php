<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\CrudController;

use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;
use PHPUnit\Framework\ExpectationFailedException;

class RenameInodeTest extends OmenApiTestCase
{
    /** @test */
    public function renameDirectory()
    {
        $this->assertRenameDirectory('new');
        $this->assertRenameDirectory('.new');
        $this->assertRenameDirectory('new');
        $this->assertRenameDirectory('.new');
        $this->assertRenameDirectory('image.jpg');
        $this->assertRenameDirectory('im.jpg');
        $this->assertRenameDirectory('image.jpg');
        $this->assertRenameDirectory('im.jpg');
        $this->assertRenameDirectory('d.t');
    }


    /** @test */
    public function renameFailDirectory()
    {
        $this->assertRenameDirectory('ne', false);
        $this->assertRenameDirectory('ne', false);
        $this->assertRenameDirectory('.ne', false);
        $this->assertRenameDirectory('.tx', false);
    }

    /** @test */
    public function renameFile()
    {
        $this->assertRenameFile('new', $this->createFile('file.txt'));
        $this->assertRenameFile('.new', $this->createFile('file.txt'));
        $this->assertRenameFile('new', $this->createFile('file'));
        $this->assertRenameFile('.new', $this->createFile('file'));
        $this->assertRenameFile('image.jpg', $this->createFile('file.txt'));
        $this->assertRenameFile('im.jpg', $this->createFile('file.txt'));
        $this->assertRenameFile('image.jpg', $this->createFile('file'));
        $this->assertRenameFile('im.jpg', $this->createFile('file'));
        $this->assertRenameFile('d.t', $this->createFile('file'));
        $this->assertRenameFile('d.t', $this->createFile('file'));
    }

    /** @test */
    public function renameFailFile()
    {
        $this->assertRenameFile('ne', $this->createFile('file.txt'), false);
        $this->assertRenameFile('ne', $this->createFile('file'), false);
        $this->assertRenameFile('.ne', $this->createFile('file.txt'), false);
        $this->assertRenameFile('.tx', $this->createFile('file'), false);
    }

    private function assertRenameDirectory($newDirectoryName, $assertSuccess = true)
    {
        $directoryPath = $this->createDirectory('directory');
        $ext = OmenHelper::mb_pathinfo($directoryPath, \PATHINFO_EXTENSION);
        $nDN = OmenHelper::filterFilename($newDirectoryName);
        $nDN = !\strlen($ext) ? \str_replace('.', '-', $nDN) : $nDN;
        $this->removeDirectory($nDN);
        $response = $this->omenApiRename($directoryPath, $newDirectoryName);
        if ($assertSuccess) {
            try {
                $response->assertOk();
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
                    'path' => sprintf('/%s', $nDN),
                    'dirName' => OmenHelper::uploadPath(),
                    'baseName' => $nDN,
                    'type' => InodeType::DIR
                ]);
                $this->assertDirectoryExists(\storage_path(sprintf('app/%s', OmenHelper::uploadPath($nDN))));
            } catch (ExpectationFailedException $e) {
                $this->fail(sprintf('Failed to assert directory has been rename %s', $e));
            }
        } else {
            try {
                $this->assertTrue(((array) $response->baseResponse)["\x00*\x00statusCode"] != 200);
                $response->assertJsonStructure([
                    'message',
                    'inodename',
                ]);
                $response->assertJsonFragment([
                    'inodename' => $nDN,
                ]);
                $this->assertDirectoryDoesNotExist(\storage_path(sprintf('app/%s', OmenHelper::uploadPath($nDN))));
            } catch (ExpectationFailedException $e) {
                $this->fail(sprintf('Failed to assert directory has not been renamed %s', $e));
            }
        }
        $this->removeDirectory($directoryPath);
        $this->removeDirectory($nDN);
    }

    private function assertRenameFile($newFileName, $filePath, $assertSuccess = true)
    {
        config(['omen.overwriteOnFileMove' => true]);
        $ext = OmenHelper::mb_pathinfo($filePath, \PATHINFO_EXTENSION);
        $response = $this->omenApiRename($filePath, $newFileName);
        $newFileName = OmenHelper::filterFilename($newFileName);
        $newFileName = !\strlen($ext) ? \str_replace('.', '-', $newFileName) : $newFileName;
        if ($assertSuccess) {
            $response->assertOk();
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
                'path' => sprintf('/%s', $newFileName),
                'dirName' => OmenHelper::uploadPath(),
                'baseName' => $newFileName,
                'type' => InodeType::FILE
            ]);
            $this->assertFileExists(\storage_path(sprintf('app/%s', OmenHelper::uploadPath($newFileName))));
        } else {
            $this->assertTrue(((array) $response->baseResponse)["\x00*\x00statusCode"] != 200);
            $response->assertJsonStructure([
                'message',
                'inodename',
            ]);
            $response->assertJsonFragment([
                'inodename' => $newFileName,
            ]);
            $this->assertFileDoesNotExist(\storage_path(sprintf('app/%s', OmenHelper::uploadPath($newFileName))));
        }
        $this->removeFile($filePath);
        $this->removeFile($newFileName);
    }
}
