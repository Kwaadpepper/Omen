<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Orchestra\Testbench\TestCase;

class OmenCSPTest extends TestCase
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
        /** TEST 1 */
        $cspRules = [
            'default-src' => ["'self'", "data:", "blob:"],
            'script-src' => ["resource://pdf.js/"],
            'style-src' => ["'self'"],
            'frame-src' => ["'self'"],
            'object-src' =>  ["'self'", "blob:"],
            'base-uri' => ["'self'", "resource://pdf.js/web/"],
            'media-src' =>  ["'self'", "blob:"]
        ];
        config(['omen.csp', $cspRules]);

        $response = $this->get(route('omenInterface', [], false));
        $response->assertStatus(200);
        if ($response['exception'] ?? null) {
            print_r($response['exception']);
        }
        $headers = $response->headers->all();
        $this->assertArrayHasKey('content-security-policy', $headers);
        $this->assertTrue($this->validateCSPRules($cspRules, $response->headers->get('content-security-policy')));

        /** TEST 2 */
        $cspRules = [
            'script-src' => ["'self'"],
            'style-src' => ["'self'"],
        ];
        config(['omen.csp', $cspRules]);
        $response = $this->get(route('omenInterface', [], false));
        $response->assertStatus(200);
        $this->assertTrue($this->validateCSPRules($cspRules, $response->headers->get('content-security-policy')));
    }

    /**
     * Parse CSP String
     * @param mixed $cspString 
     * @return array 
     */
    private function parseCSPHeader($cspString)
    {
        $csp = [];
        $directives = \explode(';', $cspString);
        foreach ($directives as $directive) {
            $rules = \explode(' ', \trim($directive));
            $directiveName = $rules[0];
            \array_shift($rules);
            $csp[$directiveName] = $rules;
        }
        return $csp;
    }

    /**
     * Check HTTP CSP response Rules against CSP rules config
     * @param mixed $cspRules 
     * @param mixed $cspString 
     * @return void 
     */
    private function validateCSPRules($cspRules, $cspString)
    {
        $rulesToCheck = $this->parseCSPHeader($cspString);

        foreach ($cspRules as $directiveName => $directiveRules) {
            if (!\array_key_exists($directiveName, $rulesToCheck)) {
                $this->fail(\sprintf('Missing CSP Directive %s in response', $directiveName));
            }
            foreach ($directiveRules as $cspRule) {
                if (!\in_array($cspRule, $cspRules[$directiveName])) {
                    $this->fail(\sprintf('Missing CSP rule %s of directive %s in response', $cspRule, $directiveName));
                }
            }
        }

        foreach ($rulesToCheck as $directiveName => $directiveRules) {
            if (
                !\array_key_exists($directiveName, $cspRules) &&
                !\in_array($directiveName, [
                    'default-src', 'style-src', 'script-src', 'report-uri'
                ])
            ) {
                $this->fail(\sprintf('Missing CSP Directive %s in rule', $directiveName));
            }
            $hadNonce = false;
            foreach ($directiveRules as $cspRule) {
                if (\strpos($cspRule, 'nonce') != -1) {
                    $hadNonce = true;
                    continue;
                }
                if (!\in_array($cspRule, $cspRules[$directiveName])) {
                    $this->fail(\sprintf('Missing CSP rule %s of directive %s in response', $cspRule, $directiveName));
                }
            }
            if (($directiveName == 'style-src' || $directiveName == 'script-src') &&
                !$hadNonce
            ) {
                $this->fail('CSP directive %s is missing nonce parameter', $directiveName);
            }
            return true;
        }
    }
}
