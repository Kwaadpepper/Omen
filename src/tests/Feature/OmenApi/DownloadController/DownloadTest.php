<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\DownloadController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class DownloadTest extends OmenApiTestCase
{
    /** @test */
    public function assertDownloadAttachementSuccess()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $response = $this->omenApiDownload('directory/file.txt');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/plain; charset=UTF-8');
        $response->assertHeader('content-length', 7);
        $response->assertHeader('content-disposition', 'attachment; filename=file.txt');
        $this->removeDirectory('directory');
    }

    /** @test */
    public function assertDownloadInlineSuccess()
    {
        $this->createDirectory('directory');
        $this->createFile('directory/file.txt');
        $response = $this->omenApiDownload('directory/file.txt', true);
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/plain; charset=UTF-8');
        $response->assertHeader('content-length', 7);
        $response->assertHeader('content-disposition', 'inline; filename=file.txt');
        $this->removeDirectory('directory');
    }

    /** @test */
    public function assertDownloadNotFound()
    {
        $response = $this->omenApiDownload('directory/file.txt');
        $response->assertStatus(404);
    }
}
