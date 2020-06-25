<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\CSRF;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\Inode;
use Kwaadpepper\Omen\Lib\InodeFileType;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\Lib\InodeVisibility;
use Kwaadpepper\Omen\Lib\LocalFile;
use Kwaadpepper\Omen\OmenHelper;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InitTest extends TestCase
{
    use ArraySubsetAsserts;

    protected function getPackageProviders($app)
    {
        return ['Kwaadpepper\Omen\Providers\OmenServiceProvider'];
    }

    /** @test */
    public function csrf()
    {
        $this->get(route('omenInterface', [], false));
        $this->assertIsString(CSRF::getHeaderName(), 'CSRF::getHeaderName');
        $this->assertIsString(CSRF::generate(), 'CSRF::generate');
        $this->assertIsString(session('omenCSRFToken'), 'CSRF::generate');
    }

    /** @test */
    public function disk()
    {
        $dirs = Storage::directories('omen');
        $this->assertEquals('omen/private', $dirs[0]);
        $this->assertEquals('omen/uploads', $dirs[1]);
    }

    /** @test */
    public function inodes()
    {
        $fm = new FileManager();
        $fileTxt = $fm->inode(OmenHelper::privatePath('file.txt'));
        $this->assertInstanceOf(Inode::class, $fileTxt);
        $fileTxt->put('Hello world');
        $this->assertEquals('txt', $fileTxt->getExtension());
        $this->assertEquals('text/plain', $fileTxt->getMimeTypeFromFileName());
        $this->assertEquals('text/plain', $fileTxt->getPossibleMimeTypesFromFileName()[0]);
        $this->assertEquals(InodeFileType::TEXT, $fileTxt->getFileType());
        $this->assertEquals(OmenHelper::privatePath(), $fileTxt->getDir());
        $this->assertEquals('private', $fileTxt->getVisibility());

        $filePdf = $fm->inode(OmenHelper::privatePath('file.pdf'));
        $this->assertEquals('pdf', $filePdf->getExtension());
        $this->assertEquals('application/pdf', $filePdf->getMimeTypeFromFileName());
        $this->assertEquals(InodeFileType::PDF, $filePdf->getFileType());

        Storage::makeDirectory(OmenHelper::privatePath('folder'));

        $directory = $fm->inode(OmenHelper::privatePath('folder'));
        $this->assertEquals('', $directory->getExtension());
        $this->assertEquals('', $directory->getMimeTypeFromFileName());
        $this->assertEquals(InodeType::DIR, $directory->getType());

        $this->assertTrue($fm->exists($fileTxt));
        $this->assertTrue($fm->exists($directory));
        $this->assertFalse($fm->exists($filePdf));

        try {
            $fm->moveTo(OmenHelper::privatePath('file.txt'), OmenHelper::uploadPath('file.txt'));
        } catch (Exception $e) {
            $this->fail($e);
        }
        $this->assertTrue($fm->exists(OmenHelper::uploadPath('file.txt')));
        $this->assertEquals($fm->inode(OmenHelper::uploadPath('file.txt'))->get(), 'Hello world');

        config(['omen.overwriteOnFileCopy' => true]);
        try {
            $fm->copyTo(OmenHelper::uploadPath('file.txt'), OmenHelper::privatePath('file2.txt'));
        } catch (Exception $e) {
            $this->fail($e);
        }
        $this->assertTrue($fm->exists(OmenHelper::privatePath('file2.txt')));
        $this->assertEquals($fm->inode(OmenHelper::privatePath('file2.txt'))->get(), 'Hello world');

        $inode = $fm->inode(OmenHelper::privatePath('file2.txt'));
        $this->assertEquals($inode->getType(), InodeType::FILE, 'Inode::getType');
        $this->assertEquals($inode->getFileType(), InodeFileType::TEXT, 'Inode::getFileType');
        $this->assertInstanceOf(LocalFile::class, $inode->getToTempLocalFile(), 'Inode::getToTempLocalFile');

        try {
            $fm->inode(OmenHelper::uploadPath('file.txt'))->delete();
        } catch (Exception $e) {
            $this->fail($e, 'Inode::delete');
        }
        try {
            $inode->append('APPEND');
        } catch (Exception $e) {
            $this->fail($e, 'Inode::append');
        }
        $this->assertTrue($fm->exists(OmenHelper::privatePath('file2.txt')), 'Inode::exists');
        $this->assertEquals($fm->inode(OmenHelper::privatePath('file2.txt'))->get(), 'Hello worldAPPEND', 'Inode::get');

        try {
            $this->assertInstanceOf(StreamedResponse::class, $inode->response(), 'Inode::response');
        } catch (OmenException | StreamedResponse | FileNotFoundException $e) {
            $this->fail($e);
        }

        $this->assertIsInt($inode->getLastModfied(), 'Inode::getLastModfied');
        $this->assertIsString($inode->getUrl(), 'Inode::getUrl');
        $this->assertEquals(OmenHelper::privatePath(), $inode->getDir(), 'Inode::getDir');
        $this->assertEquals($inode->getType(), InodeType::FILE, 'Inode::getType');
        $this->assertEquals($inode->getFileType(), InodeFileType::TEXT, 'Inode::getFileType');
        $this->assertIsString($inode->getSize(), 'Inode::getSize');
        $this->assertEquals('11B', $inode->getSize(), 'Inode::getSize');
        $this->assertIsString($inode->getDateFormated(), 'Inode::getDateFormated');
        $this->assertEquals('/file2.txt', $inode->getPath(), 'Inode::getPath');
        $this->assertEquals('file2.txt', $inode->getFullName(), 'Inode::getFullName');
        $this->assertEquals('file2', $inode->getName(), 'Inode::getName');
        try {
            $inode->setFullName('file3.txt');
        } catch (OmenException $e) {
            $this->fail($e);
        }
        $this->assertEquals(InodeVisibility::PRIVATE, $inode->getVisibility(), 'Inode::getVisibility');
        try {
            $inode->setVisibilty(InodeVisibility::PUBLIC);
        } catch (OmenException $e) {
            $this->fail($e, 'Inode::setVisibilty');
        }
        $this->assertEquals(InodeVisibility::PUBLIC, $inode->getVisibility(), 'Inode::getVisibility');
        $this->assertEquals('txt', $inode->getExtension(), 'Inode::getExtension');
        $this->assertEquals('text/plain', $inode->getMimeTypeFromFileName(), 'Inode::getMimeTypeFromFileName');
        $this->assertIsArray($inode->getPossibleMimeTypesFromFileName(), 'Inode::getPossibleMimeTypesFromFileName');
        $this->assertJson((string) $inode, 'Inode => Json Serializable');

        // === //
        try {
            $t = 'Inode::isEqualTo';
            $inode2 = $fm->inode(OmenHelper::uploadPath('file.txt'));
            $this->assertFalse($inode2->isEqualTo($inode), $t);
            $this->assertFalse($inode->isEqualTo($inode2), $t);
            $this->assertTrue($inode->isEqualTo($inode), $t);
            $this->assertTrue($inode2->isEqualTo($inode2), $t);
        } catch (OmenException $e) {
            $this->fail($e, $t);
        }


        $inodePath = \storage_path(sprintf('app/%s', $inode->getFullPath()));
        // === //
        try {
            $t = 'FileManager::isAllowedMimeType';
            $this->assertTrue($fm->isAllowedMimeType($inodePath), $t);
            config(['omen.deniedUploadExtensions' => \array_merge(config('omen.deniedUploadExtensions'), ['txt'])]);
            $this->assertFalse($fm->isAllowedMimeType($inodePath), $t);
        } catch (OmenException $e) {
            $this->fail($e, $t);
        }

        // === //
        try {
            $t = 'FileManager::checkExtensionWithMimeType';
            $deniedExts = config('omen.deniedUploadExtensions');
            unset($deniedExts['txt']);
            config(['omen.deniedUploadExtensions' => $deniedExts]);
            $this->assertTrue($fm->checkExtensionWithMimeType($inodePath), $t);
        } catch (OmenException $e) {
            $this->fail($e, $t);
        }

        // === //
        try {
            $t = 'FileManager::globFiles';
            $this->assertArraySubset(['omen/private/file3.txt'], $fm->globFiles(OmenHelper::privatePath()), $t);
        } catch (OmenException $e) {
            $this->fail($e, $t);
        }
    }
}
