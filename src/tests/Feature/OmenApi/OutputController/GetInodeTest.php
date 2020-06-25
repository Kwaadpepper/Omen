<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\OutputController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class GetInodeTest extends OmenApiTestCase
{
    /** @test */
    public function getInodeSuccess()
    {
        $response = $this->omenApiGetInode($this->createFile('test.txt'));
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'inodeHtml',
            'inode'
        ]);
        $this->removeFile('test.txt');
    }

    /** @test */
    public function getInodeFailOnNotFound()
    {
        $response = $this->omenApiGetInode('test.txt');
        $response->assertNotFound();
    }
}
