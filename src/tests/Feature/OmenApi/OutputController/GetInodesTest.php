<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\OutputController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class GetInodesTest extends OmenApiTestCase
{
    /** @test */
    public function getInodesSuccess()
    {
        $this->createDirectory('inodes');
        $this->createFile('inodes/file1.txt');
        $this->createFile('inodes/file2.txt');
        $response = $this->omenApiGetInodes('inodes');
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'inodesHtml',
            'inodes'
        ]);
        $this->removeDirectory('inodes');
    }

    /** @test */
    public function getInodesFailOnNotFound()
    {
        $response = $this->omenApiGetInodes('inodes');
        $response->assertNotFound();
    }
}
