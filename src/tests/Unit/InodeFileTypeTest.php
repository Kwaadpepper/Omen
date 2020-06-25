<?php

namespace Kwaadpepper\Omen\Tests\Unit;

use Exception;
use Kwaadpepper\Omen\Lib\InodeFileType;
use Orchestra\Testbench\TestCase;
use ReflectionObject;
use Symfony\Component\Mime\MimeTypes;

class InodeFileTypeTest extends TestCase
{
    private static $mimeTypes;

    protected function setUp(): void
    {
        parent::setUp();
        static::$mimeTypes = $this->getMimeTypes();
    }

    private function getMimeTypes()
    {
        $r = new ReflectionObject(new MimeTypes());
        return $r->getStaticProperties()['map'];
    }

    /** @test */
    public function mimetypeInfo()
    {
        foreach (static::$mimeTypes as $mimeType => $exts) {
            $mimeTypeInfo = InodeFileType::mimetypeInfo($mimeType);
            $this->assertNotEmpty($mimeTypeInfo['type'], 'type');
            $this->assertNotEmpty($mimeTypeInfo['subtype'], 'subtype');
            $this->assertIsString($mimeTypeInfo['suffix'], 'suffix');
            $this->assertIsString($mimeTypeInfo['parameter'], 'parameter');
        }
    }

    /** @test */
    public function getFromMimeType()
    {
        foreach (static::$mimeTypes as $mimeType => $exts) {
            $d = InodeFileType::getFromMimeType($mimeType);
            $this->assertTrue(\in_array(
                InodeFileType::getFromMimeType($mimeType),
                [
                    InodeFileType::ARCHIVE,
                    InodeFileType::AUDIO,
                    InodeFileType::CALC,
                    InodeFileType::DISKIMAGE,
                    InodeFileType::EXECUTABLE,
                    InodeFileType::FILE,
                    InodeFileType::IMAGE,
                    InodeFileType::IMPRESS,
                    InodeFileType::PDF,
                    InodeFileType::TEXT,
                    InodeFileType::VIDEO,
                    InodeFileType::WRITER
                ]
            ));
        }
    }
}
