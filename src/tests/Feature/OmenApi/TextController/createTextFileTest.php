<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\TextController;

use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class createTextFileTest extends OmenApiTestCase
{
    /** @test */
    public function createTextFileSuccess()
    {
        $fP = storage_path(\sprintf('app/%s', omenHelper::uploadPath('filename.txt')));
        $response = $this->omenApiCreateTextFile(
            '/',
            'filename',
        );
        $response->assertOk();
        $response->assertJson([]);
        $this->assertFileExists($fP);
        $this->assertEquals('content', File::get($fP));
        $this->removeFile('filename.txt');
    }

    /** @test */
    public function createTextFileSuccessWithEmptyContent()
    {
        $fP = storage_path(\sprintf('app/%s', omenHelper::uploadPath('filename.txt')));
        $response = $this->omenApiCreateTextFile(
            '/',
            'filename',
            ''
        );
        $response->assertOk();
        $response->assertJson([]);
        $this->assertFileExists($fP);
        $this->assertEquals('', File::get($fP));
        $this->removeFile('filename.txt');
    }

    /** @test */
    public function createTextFileFailWithTooShortFileName()
    {
        $fP = storage_path(\sprintf('app/%s', omenHelper::uploadPath('filename.txt')));
        $response = $this->omenApiCreateTextFile(
            '/',
            'fi',
            ''
        );
        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => \sprintf('File name must be at least %d long', config('omen.minimumInodeLength', 3)),
            'filename' => 'fi'
        ]);
        $this->assertFileDoesNotExist($fP);
    }
}
