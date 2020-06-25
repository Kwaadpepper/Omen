<?php

namespace Kwaadpepper\Omen\Tests\Unit;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\Disk;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\Inode;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class FileManagerTest extends OmenApiTestCase
{
    use ArraySubsetAsserts;

    /** @test */
    public function switchDiskTest()
    {
        $this->doesNotPerformAssertions();
        $fm = new FileManager();
        $fm->switchToDisk(Disk::PUBLIC);
        $fm->getDisk();
        $fm->switchToDisk(Disk::PRIVATE);
        $fm->getDisk();
    }

    private function createMockFiles()
    {
        $this->createDirectory('dir');
        $this->createFile('dir/file.txt');
        $this->createFile('dir/file2.txt');
        $this->createDirectory('dir/a');
        $this->createFile('dir/a/file.txt');
        $this->createFile('dir/a/file2.txt');
        $this->createDirectory('dir/b');
        $this->createFile('dir/b/file.txt');
        $this->createFile('dir/b/file2.txt');
    }

    private function removeMockFiles()
    {
        $this->removeDirectory('dir');
    }

    /** @test */
    public function inodes()
    {
        $this->createMockFiles();
        $fm = new FileManager();
        $inodes = $fm->inodes(OmenHelper::uploadPath('dir'));
        $inodes = \array_values((array) $inodes);
        $this->assertTrue($inodes[0]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/a'))));
        $this->assertTrue($inodes[1]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/b'))));

        $this->assertTrue($inodes[2]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/file.txt'))));
        $this->assertTrue($inodes[3]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/file2.txt'))));

        // same file, different path
        $this->assertTrue($inodes[2]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/a/file.txt'))));
        $this->assertTrue($inodes[3]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/a/file2.txt'))));

        // same file different path
        $this->assertTrue($inodes[2]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/b/file.txt'))));
        $this->assertTrue($inodes[3]->isEqualTo($fm->inode(OmenHelper::uploadPath('dir/b/file2.txt'))));
        $this->removeMockFiles();
    }

    /** @test */
    public function inode()
    {
        $this->createMockFiles();
        $fm = new FileManager();
        $inode = $fm->inode(OmenHelper::uploadPath('dir/file.txt'));
        $this->assertTrue($inode->isEqualTo(new Inode(
            OmenHelper::uploadPath('dir/file.txt'),
            InodeType::FILE,
            Storage::disk()
        )));
        $this->removeMockFiles();
    }

    /** @test */
    public function globFiles()
    {
        $this->createMockFiles();
        $fm = new FileManager();
        $files = $fm->globFiles(OmenHelper::uploadPath('dir'));
        $this->assertArraySubset([
            OmenHelper::uploadPath('dir/file.txt'),
            OmenHelper::uploadPath('dir/file2.txt')
        ], $files);
        $this->removeMockFiles();
    }

    /** @test */
    public function exists()
    {
        $this->createMockFiles();
        $fm = new FileManager();
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir')));
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir/file.txt')));
        $this->assertFalse($fm->exists(OmenHelper::uploadPath('dir/file3.txt')));
        $this->removeMockFiles();
    }

    /** @test */
    public function moveToSuccess()
    {
        $this->createMockFiles();
        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->moveTo(
            OmenHelper::uploadPath('dir/file.txt'),
            OmenHelper::uploadPath('dir/file3.txt')
        ));
        $this->assertFalse($fm->exists(OmenHelper::uploadPath('dir/file.txt')));
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir/file3.txt')));

        config(['omen.overwriteOnFileMove' => true]);

        $this->assertInstanceOf(Inode::class, $fm->moveTo(
            OmenHelper::uploadPath('dir/file2.txt'),
            OmenHelper::uploadPath('dir/file3.txt')
        ));
        $this->assertFalse($fm->exists(OmenHelper::uploadPath('dir/file2.txt')));
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir/file3.txt')));

        $this->removeMockFiles();
    }

    /** @test */
    public function moveToSuccessIfSourceIsDir()
    {
        $this->createMockFiles();
        $this->removeDirectory('dirMoved');

        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->moveTo(
            OmenHelper::uploadPath('dir'),
            OmenHelper::uploadPath('dirMoved')
        ));

        $this->assertDirectoryExists(storage_path(sprintf('app/%s', OmenHelper::uploadPath('dirMoved'))));
        $this->removeDirectory('dirMoved');

        $this->removeMockFiles();
    }

    /** @test */
    public function moveToFailIfSourceDoesNotExist()
    {
        $this->createMockFiles();

        $this->expectException(OmenException::class);
        $this->expectExceptionMessageMatches("/since it does not exists$/");
        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->moveTo(
            OmenHelper::uploadPath('dir/file4.txt'),
            OmenHelper::uploadPath('dir/file3.txt')
        ));

        $this->removeMockFiles();
    }

    /** @test */
    public function moveToFailIfDestExistsAndIsDir()
    {
        $this->createMockFiles();

        $this->expectException(OmenException::class);
        $this->expectExceptionMessageMatches("/already exists and it is a directory, move/");
        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->moveTo(
            OmenHelper::uploadPath('dir/file.txt'),
            OmenHelper::uploadPath('dir')
        ));

        $this->removeMockFiles();
    }

    /** @test */
    public function copyToSuccess()
    {
        $this->createMockFiles();
        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->copyTo(
            OmenHelper::uploadPath('dir/file.txt'),
            OmenHelper::uploadPath('dir/file3.txt')
        ));
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir/file.txt')));
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir/file3.txt')));

        config(['omen.overwriteOnFileCopy' => true]);

        $this->assertInstanceOf(Inode::class, $fm->copyTo(
            OmenHelper::uploadPath('dir/file2.txt'),
            OmenHelper::uploadPath('dir/file3.txt')
        ));
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir/file2.txt')));
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('dir/file3.txt')));

        $this->removeMockFiles();
    }

    /** @test */
    public function copyToFailIfSourceIsDir()
    {
        $this->createMockFiles();

        $this->expectException(OmenException::class);
        $this->expectExceptionMessageMatches("/since it is a directory$/");
        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->copyTo(
            OmenHelper::uploadPath('dir'),
            OmenHelper::uploadPath('dir/file3.txt')
        ));

        $this->removeMockFiles();
    }

    /** @test */
    public function copyToFailIfSourceDoesNotExist()
    {
        $this->createMockFiles();

        $this->expectException(OmenException::class);
        $this->expectExceptionMessageMatches("/since it does not exists$/");
        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->copyTo(
            OmenHelper::uploadPath('dir/file4.txt'),
            OmenHelper::uploadPath('dir/file3.txt')
        ));

        $this->removeMockFiles();
    }

    /** @test */
    public function copyToFailIfDestExistsAndIsDir()
    {
        $this->createMockFiles();

        $this->expectException(OmenException::class);
        $this->expectExceptionMessageMatches("/already exists and it is a directory, copy/");
        $fm = new FileManager();
        $this->assertInstanceOf(Inode::class, $fm->copyTo(
            OmenHelper::uploadPath('dir/file.txt'),
            OmenHelper::uploadPath('dir')
        ));

        $this->removeMockFiles();
    }

    /** @test */
    public function getNewFileNameWithStrRandom()
    {
        $this->createDirectory('dir');
        $this->createFile('dir/file.txt');
        for ($i = 1; $i < 31; $i++) {
            $this->createFile(sprintf('dir/file%d.txt', $i));
        }
        $fm = new FileManager();
        $this->assertMatchesRegularExpression(
            "/file[a-zA-Z0-9]{16}.txt$/",
            $fm->getNewFileName(OmenHelper::uploadPath('dir/file.txt'))
        );
        $this->removeDirectory(OmenHelper::uploadPath('dir'));
    }

    /** @test */
    public function createDirectorySuccess()
    {
        $dir = OmenHelper::uploadPath('dir');
        $fm = new FileManager();
        $fm->createDirectory($dir);
        $this->assertDirectoryExists(\storage_path(sprintf('app/%s', $dir)));

        $this->removeDirectory('dir');
    }

    /** @test */
    public function createRecursiveDirectorySuccess()
    {
        $dir = OmenHelper::uploadPath('dir/toto');
        $fm = new FileManager();
        $fm->createDirectory($dir);
        $this->assertDirectoryExists(\storage_path(sprintf('app/%s', $dir)));
        $this->removeDirectory('dir');
    }

    /** @test */
    public function createDirectorySuccessIfAlreadyExists()
    {
        $this->createDirectory('dirA');
        $dir = OmenHelper::uploadPath('dirA');
        $fm = new FileManager();
        $fm->createDirectory($dir);
        $this->assertDirectoryExists(\storage_path(sprintf('app/%s', $dir)));
    }

    /** @test */
    public function createDirectoryFail()
    {
        $fullPath = \storage_path(sprintf('app/%s', OmenHelper::uploadPath('dirA')));
        $this->expectException(OmenException::class);
        $this->createDirectory('dirA');
        $dir = OmenHelper::uploadPath('dirA/child');
        $this->assertTrue(\chmod($fullPath, 0));
        $fm = new FileManager();
        try {
            $fm->createDirectory($dir);
        } finally {
            $this->assertDirectoryDoesNotExist(\storage_path(sprintf('app/%s', $dir)));
            $this->assertTrue(\chmod($fullPath, 0777));
            $this->assertTrue(File::deleteDirectory($fullPath));
        }
    }

    /** @test */
    public function checkExtensionWithMimeTypeSuccess()
    {
        $this->createFile('file.txt');
        $fm = new FileManager();
        $fm->checkExtensionWithMimeType(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.txt'))));
        $this->removeFile('file.txt');
    }

    /** @test */
    public function checkExtensionWithMimeTypeError()
    {
        $this->expectException(OmenException::class);
        $this->expectExceptionMessageMatches("/does not match file name extension/");
        $this->createFile('file.jpg');
        $fm = new FileManager();
        try {
            $fm->checkExtensionWithMimeType(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.jpg'))));
        } finally {
            $this->removeFile('file.jpg');
        }
    }

    /** @test */
    public function isAllowedMimeTypeSuccess()
    {
        $this->createFile('file.txt');
        $fm = new FileManager();
        $this->assertTrue($fm->isAllowedMimeType(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.txt')))));
        $this->removeFile('file.txt');
    }

    /** @test */
    public function isAllowedMimeTypeFail()
    {
        config(['omen.deniedUploadExtensions' => ['txt']]);
        $this->createFile('file.txt');
        $fm = new FileManager();
        $this->assertFalse($fm->isAllowedMimeType(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.txt')))));
        $this->removeFile('file.txt');
    }

    /** @test */
    public function getFilePossibleExtensions()
    {
        $this->createFile('file.txt');
        $fm = new FileManager();
        $this->assertArraySubset([
            'txt', 'text'
        ], $fm->getFilePossibleExtensions(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.txt')))));
        $this->removeFile('file.txt');
    }

    /** @test */
    public function getFilePossibleExtensionsFailOnFileWrongExt()
    {
        $this->expectException(OmenException::class);
        $this->expectExceptionMessageMatches("/^File guessed extensions/");
        $this->createFile('file.jpg');
        $fm = new FileManager();
        try {
            $fm->getFilePossibleExtensions(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.jpg'))));
        } finally {
            $this->removeFile('file.jpg');
        }
    }
}
