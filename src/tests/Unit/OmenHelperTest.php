<?php

namespace Kwaadpepper\Omen\Tests\Unit;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Exception;
use Illuminate\Support\Facades\Storage;
use Kwaadpepper\Omen\Exceptions\OmenDebugException;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\Inode;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\OmenHelper;
use Orchestra\Testbench\TestCase;

class OmenHelperTest extends TestCase
{
    use ArraySubsetAsserts;

    protected function getPackageProviders($app)
    {
        return ['Kwaadpepper\Omen\Providers\OmenServiceProvider'];
    }

    /** @test */
    public function assert()
    {
        $v = \intval(2);
        OmenHelper::assert($v, 2);
        OmenHelper::assertType($v, 'integer');
        $v = \doubleval(2);
        OmenHelper::assertType($v, 'double');
        $v = \floatval(2.2);
        OmenHelper::assert($v, 2.2);
        OmenHelper::assertType($v, 'float');
        $v = 'string';
        OmenHelper::assert($v, 'string');
        OmenHelper::assertType($v, 'string');
        $v = ['roero', 'ksdksd'];
        OmenHelper::assert($v, ['roero', 'ksdksd']);
        OmenHelper::assertType($v, 'array');
        $v = null;
        OmenHelper::assert($v, null);
        OmenHelper::assertType($v, 'null');
        $v = new Exception();
        OmenHelper::assert($v, $v);
        OmenHelper::assertType($v, 'Exception');
        $v = new Inode('d', InodeType::FILE, Storage::disk());
        OmenHelper::assert($v, new Inode('d', InodeType::FILE, Storage::disk()));
        OmenHelper::assertType($v, Inode::class);

        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function assertFail()
    {
        $this->expectException(OmenDebugException::class);
        $int = 2;
        OmenHelper::assert($int, 3);
    }

    /** @test */
    public function assertFailOnArray()
    {
        $this->expectException(OmenDebugException::class);
        $v = ['roero', 'ksdksd'];
        OmenHelper::assert($v, ['roero']);
        OmenHelper::assertType($v, 'array');
    }

    /** @test */
    public function assertFailOnResource()
    {
        $this->expectException(OmenDebugException::class);
        $handle = \tmpfile();
        OmenHelper::assert($handle, $handle);
        OmenHelper::assertType($handle, 'handle');
    }

    /** @test */
    public function assertFailOnDifferentObjects()
    {
        $this->expectException(OmenDebugException::class);
        OmenHelper::assert(new FileManager(), new Inode('d', InodeType::FILE, Storage::disk()));
    }

    /** @test */
    public function assertFailOnDifferentObjectsInstances()
    {
        $this->expectException(OmenDebugException::class);
        OmenHelper::assert(new FileManager(), new FileManager());
    }

    /** @test */
    public function assertFailOnDifferentObjectsWithIsEqualTo()
    {
        $this->expectException(OmenDebugException::class);
        OmenHelper::assert(
            new Inode('d', InodeType::FILE, Storage::disk()),
            new Inode('A', InodeType::FILE, Storage::disk())
        );
    }

    /** @test */
    public function assertFailOnDifferentType()
    {
        $this->expectException(OmenDebugException::class);
        OmenHelper::assertType(
            new Inode('d', InodeType::FILE, Storage::disk()),
            new Exception()
        );
    }

    /** @test */
    public function assertFailOnNotNull()
    {
        $this->expectException(OmenDebugException::class);
        OmenHelper::assertType(null, 'integer');
    }

    /** @test */
    public function humanReadableBytes()
    {
        $this->assertIsString(OmenHelper::HumanReadableBytes(345));
        $this->assertEquals('345B', OmenHelper::HumanReadableBytes(345));
        $this->assertEquals('345KB', OmenHelper::HumanReadableBytes(345 * 1024));
    }

    /** @test */
    public function assertPregReplaceAll()
    {
        $this->assertEquals('UtestUunitUoverU', OmenHelper::preg_replace_all('/#/', 'U', '#test#unit#over#'));
    }

    /** @test */
    public function getAllowedFilesExtensions()
    {
        $ext = OmenHelper::getAllowedFilesExtensions();
        $this->assertIsArray($ext);
    }

    /** @test */
    public function mb_Alltrim()
    {
        $str = 'REMOVErandomstring';
        $str = OmenHelper::mb_ltrim($str, 'REMOVE');
        $this->assertEquals('randomstring', $str);

        $str = 'REMOVErandomstring';
        $str = OmenHelper::mb_rtrim($str, 'REMOVE');
        $this->assertEquals('REMOVErandomstring', $str);

        $str = 'REMOVErandomstring';
        $str = OmenHelper::mb_rtrim($str, 'string');
        $this->assertEquals('REMOVErandom', $str);

        $str = '读写汉字 - 学中文';
        $str = OmenHelper::mb_ltrim($str, '读写汉字');
        $this->assertEquals(' - 学中文', $str);

        $str = '读写汉字 - 学中文';
        $str = OmenHelper::mb_ltrim($str, '读写汉字 - ');
        $this->assertEquals('学中文', $str);

        $str = 'Hello world';
        $str = OmenHelper::mb_ltrim($str, 'Hdle');
        $this->assertEquals('o world', $str);

        $str = '  Hello world  ';
        $str = OmenHelper::mb_trim($str);
        $this->assertEquals('Hello world', $str);

        $this->assertEquals(trim(' foo '), OmenHelper::mb_trim(' foo '));
        $this->assertEquals(trim(' foo ', ' o'), OmenHelper::mb_trim(' foo ', ' o'));
        $this->assertEquals('foo', OmenHelper::mb_trim(' Åfooホ ', ' Åホ'));

        $this->assertEquals(rtrim(' foo '), OmenHelper::mb_rtrim(' foo '));
        $this->assertEquals(rtrim(' foo ', ' o'), OmenHelper::mb_rtrim(' foo ', ' o'));
        $this->assertEquals('foo', OmenHelper::mb_rtrim('fooホ ', ' ホ'));

        $this->assertEquals(ltrim(' foo '), OmenHelper::mb_ltrim(' foo '));
        $this->assertEquals(ltrim(' foo ', ' o'), OmenHelper::mb_ltrim(' foo ', ' o'));
        $this->assertEquals('foo', OmenHelper::mb_ltrim(' Åfoo', ' Å'));

        $str = 'file.txt';
        $str = OmenHelper::mb_rtrim($str, '.txt');
        $this->assertEquals('file', $str);
    }

    /** @test */
    public function mb_pathinfo()
    {
        $str = '/dir/to/path/image.jpg';

        $this->assertArraySubset([
            'dirname' => '/dir/to/path',
            'basename' => 'image.jpg',
            'extension' => 'jpg',
            'filename' => 'image'
        ], OmenHelper::mb_pathinfo($str));

        $this->assertEquals('/dir/to/path', OmenHelper::mb_pathinfo($str, \PATHINFO_DIRNAME));
        $this->assertEquals('image.jpg', OmenHelper::mb_pathinfo($str, \PATHINFO_BASENAME));
        $this->assertEquals('image', OmenHelper::mb_pathinfo($str, \PATHINFO_FILENAME));
        $this->assertEquals('jpg', OmenHelper::mb_pathinfo($str, \PATHINFO_EXTENSION));

        $str = '/dir/to/path/';

        $this->assertArraySubset([
            'dirname' => '/dir/to',
            'basename' => 'path',
            'filename' => 'path',
        ], OmenHelper::mb_pathinfo($str));

        $this->assertEquals('/dir/to', OmenHelper::mb_pathinfo($str, \PATHINFO_DIRNAME));
        $this->assertEquals('path', OmenHelper::mb_pathinfo($str, \PATHINFO_BASENAME));
        $this->assertEquals('path', OmenHelper::mb_pathinfo($str, \PATHINFO_FILENAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_EXTENSION));

        $str = '';

        $this->assertArraySubset([], pathinfo($str));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_DIRNAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_BASENAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_FILENAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_EXTENSION));

        $str = '/';

        $this->assertArraySubset([
            'dirname' => '/'
        ], OmenHelper::mb_pathinfo($str));
        $this->assertEquals('/', OmenHelper::mb_pathinfo($str, \PATHINFO_DIRNAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_BASENAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_FILENAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_EXTENSION));

        $str = '/dir';

        $this->assertArraySubset([
            'dirname' => '/',
            'basename' => 'dir',
            'filename' => 'dir'
        ], OmenHelper::mb_pathinfo($str));
        $this->assertEquals('/', OmenHelper::mb_pathinfo($str, \PATHINFO_DIRNAME));
        $this->assertEquals('dir', OmenHelper::mb_pathinfo($str, \PATHINFO_BASENAME));
        $this->assertEquals('dir', OmenHelper::mb_pathinfo($str, \PATHINFO_FILENAME));
        $this->assertEquals('', OmenHelper::mb_pathinfo($str, \PATHINFO_EXTENSION));
    }

    /** @test */
    public function beautifyFilename()
    {
        $this->assertEquals(OmenHelper::filterFilename('file   name.zip'), 'file-name.zip');
        $this->assertEquals(OmenHelper::filterFilename('file___name.zip'), 'file-name.zip');
        $this->assertEquals(OmenHelper::filterFilename('file---name.zip'), 'file-name.zip');
        $this->assertEquals(OmenHelper::filterFilename('file--.--.-.--name.zip'), 'file.name.zip');
        $this->assertEquals(OmenHelper::filterFilename('file...name..zip'), 'file.name.zip');
        $this->assertEquals(OmenHelper::filterFilename('FILE.ZIP'), 'file.zip');
        $this->assertEquals(OmenHelper::filterFilename('.file-name.-'), 'file-name');
        $this->assertEquals(OmenHelper::filterFilename('.hidden'), 'hidden');
        $this->assertEquals(OmenHelper::filterFilename('...hidden'), 'hidden');
        $this->assertEquals(OmenHelper::filterFilename('...'), '');
        $this->assertLessThan(256, \strlen(OmenHelper::filterFilename(\str_repeat('longpathDir', 50) . '.jpg')));
        $this->assertEquals('jpg', OmenHelper::mb_pathinfo(\str_repeat('longpathDir', 50) . '.jpg', \PATHINFO_EXTENSION));
    }

    /** @test */
    public function sanitizePath()
    {
        $this->assertEquals('/dir/di/.dir/di/file.jpg/dir', OmenHelper::sanitizePath('///dir/di/.dir/di/file.jpg/dir'));
        $this->assertEquals('/', OmenHelper::sanitizePath('//'));
        $this->assertEquals('/', OmenHelper::sanitizePath('/////'));
        $this->assertEquals('/.file.jpg', OmenHelper::sanitizePath('/.file.jpg'));
    }

    /** @test */
    public function filterPath()
    {
        config(['omen.privatePath' => 'omen/private']);
        $this->assertEquals('omen/private/sdsd/../.QSD/sd', OmenHelper::privatePath('/sdsd/../.QSD/sd'));
        $this->assertEquals('omen/private/sdsd', OmenHelper::privatePath('/sdsd'));
        $this->assertEquals('omen/private', OmenHelper::privatePath('/'));
        $this->assertEquals('omen/private', OmenHelper::privatePath());
        $this->assertEquals('omen/private', OmenHelper::privatePath());
        config(['omen.publicPath' => 'omen/public']);
        $this->assertEquals('omen/public/sdsd/../.QSD/sd', OmenHelper::uploadPath('/sdsd/../.QSD/sd'));
        $this->assertEquals('omen/public/sdsd', OmenHelper::uploadPath('/sdsd'));
        $this->assertEquals('omen/public', OmenHelper::uploadPath('/'));
        $this->assertEquals('omen/public', OmenHelper::uploadPath());
        $this->assertEquals('omen/public', OmenHelper::uploadPath());
    }
}
