<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\ImageController;

use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class ResizeTest extends OmenApiTestCase
{
    protected function getPackageProviders($app)
    {
        return array_merge(
            parent::getPackageProviders($app),
            ['Intervention\Image\ImageServiceProviderLaravelRecent']
        );
    }
    /** @test */
    public function assertResizeSuccess()
    {
        $this->resize(80, 80);
    }

    /** @test */
    public function assertResizeFailOnDisabled()
    {
        config(['omen.fileOperationImageDriver' => false]);
        $this->resize(80, 80, false);
    }

    /** @test */
    public function assertResizeFailOnDisabledBadRequest()
    {
        $this->resize(80, ':)', false);
        $response = $this->omenApiRequest('POST', route('OmenApi.omenResizeImage', [], false));
        $this->assertTrue(((array) $response->baseResponse)["\x00*\x00statusCode"] != 200);
        $response = $this->omenApiResize('dontexists.png', 80, 80);
        $this->assertTrue(((array) $response->baseResponse)["\x00*\x00statusCode"] != 200);
    }

    private function resize($fileheight, $filewidth, bool $assertSuccess = true)
    {
        $filepath = $this->genImage();
        $response = $this->omenApiResize($filepath, $fileheight, $filewidth);
        if ($assertSuccess) {
            $response->assertSuccessful();
        } else {
            $this->assertTrue(((array) $response->baseResponse)["\x00*\x00statusCode"] != 200);
        }
        $this->clearImage();
    }
}
