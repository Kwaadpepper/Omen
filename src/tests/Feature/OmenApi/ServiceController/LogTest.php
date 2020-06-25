<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\ServiceController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class LogTest extends OmenApiTestCase
{
    /** @test */
    public function log()
    {
        $response = $this->omenApiLog(200, 'message test string');
        $response->assertStatus(200);
    }
}
