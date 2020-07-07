<?php

namespace Kwaadpepper\Omen\Tests\Browser\SortTest;

use Kwaadpepper\Omen\Tests\Browser\BrowserTestCase;
use Laravel\Dusk\Browser;

class SortTest extends BrowserTestCase
{
    /** @test */
    public function sort()
    {
        $this->browse(function (Browser $browser) {
            $response = $browser->visit(config('omen.urlPrefix'))
                ->waitUntil('omenLoaded')
                ->press('#fileSortButton')
                ->press('#sortAlpha')
                ->press('#fileSortButton')
                ->press('#sortAlpha');
            // dd($browser->driver->manage()->getLog('browser'));
            $this->assertEmpty($browser->driver->manage()->getLog('browser'));
        });
    }
}
