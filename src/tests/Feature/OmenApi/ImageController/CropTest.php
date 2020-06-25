<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\ImageController;

use Exception;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class CropTest extends OmenApiTestCase
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
        $this->crop(0, 0, 80, 80);
        $this->crop(0, 0, 300, 300);
        $this->crop(0, 0, 1, 1);
        $this->crop(0, 0, 20, 20, -23, 0, 0);
        $this->crop(0, 0, 2, 2, 0, -3.3, 0, true);
    }

    /** @test */
    public function assertResizeFailOnInvalidCommand()
    {
        $this->crop(0, 0, 0, 0, 0, 0, 0, false);
        $this->crop(0, 0, -20, -20, 0, 0, 0, false);
        $this->crop(0, 0, 2, 2, 0, 0, 'sd', false);
        $this->crop(0, 'ere', 2, 2, 0, 0, 0, false);
    }

    private function crop($xCoord, $yCoord, $width, $height, $rotate = 0, $scaleX = 0, $scaleY = 0, $assertSuccess = true)
    {
        $filepath = $this->genImage();
        $response = $this->omenApiCrop($filepath, $xCoord, $yCoord, $width, $height, $rotate, $scaleX, $scaleY);
        if ($assertSuccess) {
            $response->assertSuccessful();
        } else {
            $this->assertTrue(((array) $response->baseResponse)["\x00*\x00statusCode"] != 200);
        }
        $this->clearImage();
    }
}
