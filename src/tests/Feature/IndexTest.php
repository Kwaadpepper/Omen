<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Orchestra\Testbench\TestCase;

class IndexTest extends TestCase
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
        $response = $this->get('/omenfilemanager');

        $response->assertStatus(200);

        if ($response['exception'] ?? null) {
            print_r($response['exception']);
        }

        $headers = $response->headers->all();

        $this->assertArrayHasKey('content-type', $headers);
        $this->assertArrayHasKey('cache-control', $headers);
        $this->assertArrayHasKey('date', $headers);
        $this->assertArrayHasKey('set-cookie', $headers);
        $this->assertArrayHasKey('content-security-policy', $headers);
        $this->assertArrayHasKey('x-frame-options', $headers);
        $this->assertArrayHasKey('x-content-type-options', $headers);
        $this->assertArrayHasKey('referrer-policy', $headers);
        $this->assertArrayHasKey('feature-policy', $headers);
        $this->assertArrayHasKey('x-ratelimit-limit', $headers);
        $this->assertArrayHasKey('x-ratelimit-remaining', $headers);

        $this->assertEquals('text/html; charset=UTF-8', $headers['content-type'][0]);
        $this->assertEquals('no-cache, private', $headers['cache-control'][0]);
        $this->assertEquals('nosniff', $headers['x-content-type-options'][0]);
    }
}
