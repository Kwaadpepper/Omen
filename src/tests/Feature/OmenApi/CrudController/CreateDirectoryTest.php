<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\CrudController;

use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\Lib\InodeType;
use Kwaadpepper\Omen\Lib\InodeVisibility;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class CreateDirectoryTest extends OmenApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // make sure to remove existent files
        File::deleteDirectory(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('directoryname'))));
        File::deleteDirectory(\storage_path(sprintf('app/%s', OmenHelper::uploadPath('directorytocreate'))));
    }

    /** @test */
    public function assertCreateDirectorySuccess()
    {
        $dirName = 'directoryName';
        $response = $this->omenApiCreateDirectory('/', $dirName);
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
            'name' => \strtolower($dirName),
            'path' => sprintf('/%s', \strtolower($dirName)),
            'dirName' => OmenHelper::uploadPath(),
            'baseName' => \strtolower($dirName),
            'url' => null,
            'type' => InodeType::DIR,
            'extension' => false,
            'fileType' => null,
            'mimeType' => false,
            'size' => false,
            'visibility' => InodeVisibility::PRIVATE
        ]);
        $response->assertJson([]);

        $this->assertDirectoryExists(\storage_path(sprintf('app/%s', OmenHelper::uploadPath(\strtolower($dirName)))));
    }

    /** @test */
    public function assertCreateRecursiveDirectorySuccess()
    {
        $dirName = 'childrenDir';
        $parent = 'directoryToCreate';
        $fullPath = sprintf('%s/%s', $parent, $dirName);
        $response = $this->omenApiCreateDirectory(sprintf('/%s', $parent), $dirName);
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
            'name' => \strtolower($dirName),
            'path' => sprintf('/%s', \strtolower($fullPath)),
            'dirName' => \strtolower(OmenHelper::uploadPath($parent)),
            'baseName' => \strtolower($dirName),
            'url' => null,
            'type' => InodeType::DIR,
            'extension' => false,
            'fileType' => null,
            'mimeType' => false,
            'size' => false,
            'visibility' => InodeVisibility::PRIVATE
        ]);
        $response->assertJson([]);

        $this->assertDirectoryExists(\storage_path(sprintf('app/%s', OmenHelper::uploadPath(\strtolower($fullPath)))));
    }

    /** @test */
    public function assertCreateWithInsufisentLengthFail()
    {
        $dirName = 'di';
        $response = $this->omenApiCreateDirectory('/', $dirName);
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'message',
            'filename'
        ]);
        $response->assertJsonFragment([
            'filename' => \strtolower($dirName),
        ]);
        $response->assertJson([]);

        $this->assertDirectoryDoesNotExist(\storage_path(sprintf('app/%s', OmenHelper::uploadPath(\strtolower($dirName)))));
    }
}
