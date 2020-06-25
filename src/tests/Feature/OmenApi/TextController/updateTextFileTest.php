<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\TextController;

use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class updateTextFileTest extends OmenApiTestCase
{
    /** @test */
    public function updateTextFileSuccess()
    {
        $this->createFile('filename.txt');
        $fP = storage_path(\sprintf('app/%s', omenHelper::uploadPath('filename.txt')));
        $response = $this->omenApiUpdateTextFile(
            '/filename.txt',
            'updated content',
        );
        $response->assertOk();
        $response->assertJson([]);
        $this->assertFileExists($fP);
        $this->assertEquals('updated content', File::get($fP));
        $this->removeFile('filename.txt');
    }

    /** @test */
    public function updateTextFileSuccessWithEmptyContent()
    {
        $this->createFile('filename.txt');
        $fP = storage_path(\sprintf('app/%s', omenHelper::uploadPath('filename.txt')));
        $response = $this->omenApiUpdateTextFile(
            '/filename.txt',
            '',
        );
        $response->assertOk();
        $response->assertJson([]);
        $this->assertFileExists($fP);
        $this->assertEquals('', File::get($fP));
        $this->removeFile('filename.txt');
    }

    /** @test */
    public function updateTextFileSuccessIfFileDosNotExists()
    {
        $fP = storage_path(\sprintf('app/%s', omenHelper::uploadPath('filename.txt')));
        $response = $this->omenApiUpdateTextFile(
            '/filename.txt',
            'content',
        );
        $response->assertOk();
        $response->assertJson([]);
        $this->assertFileExists($fP);
        $this->assertEquals('content', File::get($fP));
        $this->removeFile('filename.txt');
    }
}
