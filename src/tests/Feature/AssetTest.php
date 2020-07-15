<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use Kwaadpepper\Omen\Lib\CSRF;
use LogicException;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use PHPUnit\Framework\Exception;

class AssetTest extends TestCase
{
    private static $omenCSRFToken = null;
    private static $CSRFCOOKIE = null;

    protected function getPackageProviders($app)
    {
        return ['Kwaadpepper\Omen\Providers\OmenServiceProvider'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        \config(['app.debug' => true]);
        $response = $this->call('GET', route('omenInterface', [], false));

        static::$CSRFCOOKIE = \urldecode(\explode('=', \explode(';', $response->headers->get('set-cookie'))[0])[1]);
        static::$omenCSRFToken = session()->get('omenCSRFToken');
    }

    /** @test */
    public function cssAsset()
    {
        $this->checkAsset('/vendor/omen/css/omen.css', 'text/css; charset=UTF-8');
    }

    /** @test */
    public function jsAsset()
    {
        $this->checkAsset('/vendor/omen/js/omen.js', 'text/javascript; charset=UTF-8');
        $this->checkAsset('/vendor/omen/js/manifest.js', 'text/javascript; charset=UTF-8');
        $this->checkAsset('/vendor/omen/js/vendor.js', 'text/javascript; charset=UTF-8');
        $this->checkAsset('/vendor/omen/js/vendor/mediaelement.min.js', 'text/javascript; charset=UTF-8');
    }

    /** @test */
    public function imageAsset()
    {
        $this->checkAsset('/vendor/omen/images/favicon/favicon-194x194.png', 'image/png');
        $this->checkAsset('/vendor/omen/images/favicon/favicon-32x32.png', 'image/png');
        $this->checkAsset('/vendor/omen/images/favicon/favicon-16x16.png', 'image/png');
        $this->checkAsset('/vendor/omen/images/favicon/apple-touch-icon.png', 'image/png');
        $this->checkAsset('/vendor/omen/images/favicon/android-chrome-192x192.png', 'image/png');
        $this->checkAsset('/vendor/omen/images/favicon/favicon.ico', 'application/ico');
    }

    public function fontAsset()
    {
        $this->checkAsset('/vendor/omen/fonts/materialdesignicons-webfont.woff2?v=5.3.45', 'image/x-icon');
    }

    /**
     * Check if asset can be retrieved
     * @param string $url 
     * @param string $contentType 
     * @return void 
     * @throws BindingResolutionException 
     * @throws LogicException 
     * @throws BadRequestException 
     * @throws ExpectationFailedException 
     * @throws InvalidArgumentException 
     * @throws Exception 
     */
    private function checkAsset(string $url, string $contentType)
    {
        $response = $this->call('GET', $url, [], [
            'XSRF-TOKEN' => static::$CSRFCOOKIE
        ], [], [
            sprintf('HTTP_%s', CSRF::getHeaderName()) => static::$omenCSRFToken
        ]);

        static::$omenCSRFToken = $response->headers->get(CSRF::getHeaderName());
        $response->assertStatus(200);
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

        $this->assertEquals(\sprintf('%s', $contentType), $headers['content-type'][0]);
        $this->assertEquals('no-cache, private', $headers['cache-control'][0]);
        $this->assertEquals('nosniff', $headers['x-content-type-options'][0]);
    }
}
