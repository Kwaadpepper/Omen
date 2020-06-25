<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Orchestra\Testbench\TestCase;

class OmenXContentTypeOptionTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['Kwaadpepper\Omen\Providers\OmenServiceProvider'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        \config(['app.debug' => true]);
    }

    /** @test */
    public function index()
    {
        $response = $this->get(route('omenInterface', [], false));
        $response->assertStatus(200);
        if ($response['exception'] ?? null) {
            print_r($response['exception']);
        }
        $headers = $response->headers->all();
        $this->assertArrayHasKey('x-content-type-options', $headers);
        $this->assertTrue($response->headers->get('x-content-type-options') == 'nosniff', \sprintf(
            'x-content-type-options should be equal to %s, %s found instead',
            'nosniff',
            $response->headers->get('x-content-type-options')
        ));
    }
}
