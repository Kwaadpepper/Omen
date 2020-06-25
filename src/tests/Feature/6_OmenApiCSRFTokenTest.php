<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Kwaadpepper\Omen\Lib\CSRF;
use Orchestra\Testbench\TestCase;

class OmenApiCSRFTokenTest extends TestCase
{
    private static $omenCSRFToken = null;
    private static $CSRFCOOKIE = null;

    protected function getPackageProviders($app)
    {
        return [
            'Kwaadpepper\Omen\Providers\OmenServiceProvider',
        ];
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
    public function cookie()
    {
        $response = $this->call('POST', route('OmenApi.omenPing', [], false), [], [
            'XSRF-TOKEN' => static::$CSRFCOOKIE
        ], [], [
            sprintf('HTTP_%s', CSRF::getHeaderName()) => static::$omenCSRFToken
        ]);
        $response->assertJson([]);

        $response = $this->call('POST', route('OmenApi.omenPing', [], false), [], [
            'XSRF-TOKEN' => static::$CSRFCOOKIE
        ], [], [
            sprintf('HTTP_%s', CSRF::getHeaderName()) => 'WRONGCSRFTOKEN'
        ]);
        $response->assertStatus(419);
    }
}
