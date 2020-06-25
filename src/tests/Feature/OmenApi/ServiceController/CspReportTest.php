<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\ServiceController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class CspReportTest extends OmenApiTestCase
{
    /** @test */
    public function cspReport()
    {
        $response = $this->omenApiCspReport('message test string');
        $response->assertStatus(200);
    }
}
