<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\ServiceController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class PingTest extends OmenApiTestCase
{
    /** @test */
    public function index()
    {
        $response = $this->omenApiPing();
        $response->assertStatus(200);
    }
}
