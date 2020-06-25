<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Orchestra\Testbench\TestCase;

class OmenReferrerPolicyTest extends TestCase
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
        $this->assertArrayHasKey('referrer-policy', $headers);
        $this->assertTrue($response->headers->get('referrer-policy') == 'no-referrer', \sprintf(
            'referrer-policy should be equal to %s, %s found instead',
            'no-referrer',
            $response->headers->get('referrer-policy')
        ));
    }
}
