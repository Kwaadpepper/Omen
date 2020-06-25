<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Orchestra\Testbench\TestCase;

class OmenFeaturePolicyTest extends TestCase
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
        $featurePolicy = \implode(
            ' ',
            [
                "autoplay 'self';",
                "fullscreen 'self';",
                "layout-animations 'self';",
                "legacy-image-formats 'self';",
                "midi 'self';",
                "navigation-override 'self';",
                "oversized-images 'self';",
                "picture-in-picture 'self';",
                "sync-xhr 'self';",
                "accelerometer 'none';",
                "ambient-light-sensor 'none';",
                "battery 'none';",
                "camera 'none';",
                "display-capture 'none';",
                "document-domain 'none';",
                "encrypted-media 'none';",
                "execution-while-not-rendered 'none';",
                "execution-while-out-of-viewport 'none';",
                "geolocation 'none';",
                "gyroscope 'none';",
                "magnetometer 'none';",
                "microphone 'none';",
                "payment 'none';",
                "publickey-credentials-get 'none';",
                "usb 'none';",
                "vr 'none';",
                "wake-lock 'none';",
                "xr-spatial-tracking 'none';"
            ]
        );
        $response = $this->get(route('omenInterface', [], false));
        $response->assertStatus(200);
        if ($response['exception'] ?? null) {
            print_r($response['exception']);
        }
        $headers = $response->headers->all();
        $this->assertArrayHasKey('feature-policy', $headers);
        $this->assertTrue($response->headers->get('feature-policy') == $featurePolicy, \sprintf(
            'feature-policy should be equal to %s, %s found instead',
            $featurePolicy,
            $response->headers->get('feature-policy')
        ));
    }
}
