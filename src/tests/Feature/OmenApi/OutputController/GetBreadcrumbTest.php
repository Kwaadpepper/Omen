<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\OutputController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class GetBreadcrumbTest extends OmenApiTestCase
{
    /** @test */
    public function GetBreadcrumbSuccess()
    {
        $this->createDirectory('inodes');
        $this->createFile('inodes/file1.txt');
        $this->createFile('inodes/file2.txt');
        $response = $this->omenApiGetBreadcrumb('inodes');
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'breadcrumbHtml',
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
