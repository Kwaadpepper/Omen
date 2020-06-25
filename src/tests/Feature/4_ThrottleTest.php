<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Exception;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Kwaadpepper\Omen\Lib\CSRF;
use Orchestra\Testbench\TestCase;

class ThrottleTest extends TestCase
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
        $response = $this->get(route('omenInterface', [], false));

        static::$CSRFCOOKIE = \urldecode(\explode('=', \explode(';', $response->headers->get('set-cookie'))[0])[1]);
        static::$omenCSRFToken = session()->get('omenCSRFToken');
    }

    /** @test */
    public function throttle()
    {
        $this->expectException(ThrottleRequestsException::class);
        $i = 0;
        do {
            $response = $this->call('POST', route('OmenApi.omenPing', [], false), [], [
                'XSRF-TOKEN' => static::$CSRFCOOKIE
            ], [], [
                sprintf('HTTP_%s', CSRF::getHeaderName()) => static::$omenCSRFToken
            ]);
            static::$omenCSRFToken = $response->headers->get(CSRF::getHeaderName());
            $response->assertJson([]);
        } while ($i++ < 100);
    }
}
