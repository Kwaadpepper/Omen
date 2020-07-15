<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Orchestra\Testbench\TestCase;

class OmenXFrameOptionTest extends TestCase
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
        // false => deny, true => sameorigin
        config(['omen.useXFrameOptions' => false]);

        $response = $this->get(route('omenInterface', [], false));
        $response->assertStatus(200);
        if ($response['exception'] ?? null) {
            print_r($response['exception']);
        }
        $headers = $response->headers->all();
        $this->assertArrayHasKey('x-frame-options', $headers);
        $this->assertTrue($response->headers->get('x-frame-options') == 'deny', \sprintf(
            'x-frame-options should be equal to %s, %s found instead',
            'deny',
            $response->headers->get('x-frame-options')
        ));

        // false => deny, true => sameorigin
        config(['omen.useXFrameOptions' => true]);

        $response = $this->get(route('omenInterface', [], false));
        $response->assertStatus(200);
        if ($response['exception'] ?? null) {
            print_r($response['exception']);
        }
        $headers = $response->headers->all();
        $this->assertArrayHasKey('x-frame-options', $headers);
        $this->assertTrue($response->headers->get('x-frame-options') == 'sameorigin', \sprintf(
            'x-frame-options should be equal to %s, %s found instead',
            'sameorigin',
            $response->headers->get('x-frame-options')
        ));
    }
}
