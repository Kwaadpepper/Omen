<?php

namespace Kwaadpepper\Omen\Tests\Feature;

use Exception;
use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Kwaadpepper\Omen\Lib\FileManager;
use Kwaadpepper\Omen\Lib\ImageLib;
use Kwaadpepper\Omen\Lib\Inode;
use Kwaadpepper\Omen\OmenHelper;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class ImageLibTest extends TestCase
{
    private static $image;

    protected function getPackageProviders($app)
    {
        return [
            'Intervention\Image\ImageServiceProviderLaravelRecent',
            'Kwaadpepper\Omen\Providers\OmenServiceProvider',
        ];
    }

    /**
     * Generate a static image
     * @return Inode|void 
     * @throws OmenException 
     */
    private function genImage()
    {
        $fm = new FileManager();
        $imageContent = File::get(\sprintf('%s/../resources/image.png', __DIR__));
        static::$image = $fm->inode(OmenHelper::privatePath('image.png'));
        static::$image->put($imageContent);
    }

    private function clearImage()
    {
        static::$image->delete();
        static::$image = null;
    }

    /** @test */
    public function rotate()
    {
        try {
            $this->genImage();
            $this->assertTrue(ImageLib::rotate(static::$image, 15), 'ImageLib::rotate');
            $this->assertTrue(ImageLib::applyOn(static::$image, [
                'function' => 'rotate',
                'args' => ['angle' => 30]
            ]), 'ImageLib::applyOn => rotate');
            $this->clearImage();
        } catch (Exception $e) {
            $this->fail(\sprintf('ImageLib::rotate %s', $e));
        }
    }

    /** @test */
    public function flip()
    {
        try {
            $this->genImage();
            $this->assertTrue(ImageLib::flip(static::$image, true), 'ImageLib::flip');
            $this->assertTrue(ImageLib::applyOn(static::$image, [
                'function' => 'flip',
            ]), 'ImageLib::flip');
            $this->clearImage();
        } catch (Exception $e) {
            $this->fail(\sprintf('ImageLib::flip %s', $e));
        }
    }

    /** @test */
    public function crop()
    {
        try {
            $this->genImage();
            $this->assertTrue(ImageLib::crop(static::$image, 3, 5, 90, 90), 'ImageLib::crop');
            $this->assertTrue(ImageLib::applyOn(static::$image, [
                'function' => 'crop',
                'args' => [
                    'cropX' => 3,
                    'cropY' => 5,
                    'width' => 30,
                    'height' => 30,
                ]
            ]), 'ImageLib::crop');
            $this->clearImage();
        } catch (Exception $e) {
            $this->fail(\sprintf('ImageLib::crop %s', $e));
        }
    }

    /** @test */
    public function resize()
    {
        try {
            $this->genImage();
            $this->assertTrue(ImageLib::resize(static::$image, 90, 90), 'ImageLib::resize');
            $this->assertTrue(ImageLib::applyOn(static::$image, [
                'function' => 'resize',
                'args' => [
                    'width' => 30,
                    'height' => 30,
                ]
            ]), 'ImageLib::resize');
            $this->clearImage();
        } catch (Exception $e) {
            $this->fail(\sprintf('ImageLib::resize %s', $e));
        }
    }
}
