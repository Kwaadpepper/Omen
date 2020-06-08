<?php

namespace Kwaadpepper\Omen\Lib;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Intervention\Image\Facades\Image;
use \Intervention\Image\Image as InterventionImage;
use Kwaadpepper\Omen\OmenHelper;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as ExceptionFileNotFoundException;

/**
 * Provides operations such as resize, crop, rotate and flip for images
 * using Intervention lib
 * @package Kwaadpepper\Omen\Lib
 */
class ImageLib
{
    /**
     * Applies different operations on images
     * supports 'resize', 'crop', 'rotate', 'flip'
     * Any operation should be given as an array in $operations
     * 
     * eg:
     * $operations = ['function' => 'rotate', 'args' => ['angle' => 25]];
     * 
     * Arguments for the operations are :
     *   - resize(float $width, float $height)
     *   - crop(float $cropX, float $cropY, float $width, float $height)
     *   - rotate(float $angle)
     *   - flip(bool $vertical)
     * 
     * @param Inode $image The image to modify
     * @param array $operations The operations to apply
     * @param Inode|null $newImage If the image should be saved in another Inode
     * @return bool false If an error occured, true otherwise
     */
    public static function applyOn(Inode $image, array $operations, Inode $newImage = null)
    {
        try {
            // CHECKS
            OmenHelper::assert($image->getType(), InodeType::FILE);
            OmenHelper::assert($image->getFileType(), InodeFileType::IMAGE);
            if ($newImage) {
                OmenHelper::assert($newImage->getType(), InodeType::FILE);
                OmenHelper::assert($newImage->getFileType(), InodeFileType::IMAGE);
            }

            // INIT INTERVENTION
            $interventionImage = Image::make($image->get());

            if (!array_key_exists('function', $operations)) {
                foreach ($operations as $operation) {
                    $function = $operation['function'] ?? null;
                    $args = $operation['args'] ?? [];
                    \call_user_func(__CLASS__ . "::_$function", $interventionImage, $args);
                }
            } else {
                $function = $operations['function'] ?? null;
                $args = $operations['args'] ?? [];
                \call_user_func(__CLASS__ . "::_$function", $interventionImage, $args);
            }
        } catch (OmenException $e) {
            \report($e);
            return false;
        } catch (FileNotFoundException $e) {
            \report($e);
            return false;
        }

        $data = $interventionImage->encode();

        // release Intervention memory
        $interventionImage->destroy();

        try {
            if (!$newImage) {
                $image->put($data);
            } else {
                $newImage->put($data);
            }
        } catch (ExceptionFileNotFoundException $e) {
            \report($e);
            return false;
        }

        return true;
    }

    /**
     * Try to resize an Image
     * @param Inode $image 
     * @param float $width 
     * @param float $height 
     * @param Inode|null $newImage 
     * @return bool false If an error occured, true otherwise
     */
    public static function resize(Inode $image, float $width, float $height, Inode $newImage = null)
    {
        return static::applyOn($image, [
            'function' => 'resize',
            'args' => ['width' => $width, 'height' => $height]
        ], $newImage);
    }

    /**
     * Try to crop an image
     * @param Inode $image 
     * @param float $cropX 
     * @param float $cropY 
     * @param float $width 
     * @param float $height 
     * @param Inode|null $newImage 
     * @return bool false If an error occured, true otherwise
     */
    public static function crop(Inode $image, float $cropX, float $cropY, float $width, float $height, Inode $newImage = null)
    {
        return static::applyOn($image, [
            'function' => 'resize',
            'args' => [
                'cropX' => $cropX,
                'cropY' => $cropY,
                'width' => $width,
                'height' => $height,
            ]
        ], $newImage);
    }

    /**
     * Try to rotate an image
     * @param Inode $image 
     * @param float $angle 
     * @param Inode|null $newImage 
     * @return bool false If an error occured, true otherwise
     */
    public static function rotate(Inode $image, float $angle, Inode $newImage = null)
    {
        if (!$angle) {
            return true;
        }
        return static::applyOn($image, [
            'function' => 'rotate',
            'args' => ['angle' => $angle]
        ], $newImage);
    }

    /**
     * Try to flip an image
     * @param Inode $image 
     * @param bool $vertical false to flip horizontal, true to flip vertical
     * @param Inode|null $newImage 
     * @return bool false If an error occured, true otherwise
     */
    public static function flip(Inode $image, bool $vertical, Inode $newImage = null)
    {
        return static::applyOn($image, [
            'function' => 'flip',
            'args' => ['vertical' => $vertical]
        ], $newImage);
    }

    private static function _resize(InterventionImage $image, array $args)
    {
        $width = $args['width'] ?? null;
        $height = $args['height'] ?? null;
        OmenHelper::assertType($width, 'float');
        OmenHelper::assertType($height, 'float');

        $initialMemory = ini_get('memory_limit');
        static::allocMemory($width, $height);

        $image->resize($width, $height);

        ini_set('memory_limit', $initialMemory);
    }

    private static function _crop(InterventionImage $image, array $args)
    {
        $width = \floor($args['width']) ?? null;
        $height = \floor($args['height']) ?? null;
        $cropX = \floor($args['cropX']) ?? null;
        $cropY = \floor($args['cropY']) ?? null;
        OmenHelper::assertType($width, 'integer');
        OmenHelper::assertType($height, 'integer');
        OmenHelper::assertType($cropX, 'integer');
        OmenHelper::assertType($cropY, 'integer');

        $initialMemory = ini_get('memory_limit');
        static::allocMemory($width, $height);

        $image->crop($width, $height, $cropX, $cropY);

        ini_set('memory_limit', $initialMemory);
    }

    private static function _rotate(InterventionImage $image, array $args)
    {
        $angle = $args['angle'] ?? null;
        OmenHelper::assertType($angle, 'float');

        $initialMemory = ini_get('memory_limit');
        static::allocMemory($image->getWidth(), $image->getHeight());

        $image->rotate($angle);

        ini_set('memory_limit', $initialMemory);
    }

    private static function _flip(InterventionImage $image, array $args)
    {
        $vertical = $args['vertical'] ?? false;
        OmenHelper::assertType($vertical, 'boolean');

        $initialMemory = ini_get('memory_limit');
        static::allocMemory($image->getWidth(), $image->getHeight());

        $image->flip($vertical ? 'v' : 'h');

        ini_set('memory_limit', $initialMemory);
    }


    /**
     * https://alvarotrigo.com/blog/allocate-memory-on-the-fly-PHP-image-resizing/
     * @param float $width 
     * @param float $height 
     * @return void
     */
    private static function allocMemory(float $width, float $height)
    {
        if (!config('omen.fileOperationMemoryAlloc')) {
            return;
        }

        \set_time_limit(\config('omen.fileOperationTimeLimit', 30));

        //initializing variables

        $maxMemoryUsage = \intval(\rtrim(config('omen.fileOperationMaxMemoryAlloc'), 'M'));
        $size = \intval(\rtrim(\ini_get('memory_limit'), 'M'));

        //calculating the needed memory
        $size = $size + \floor(($width * $height * 4 * 1.5 + 1048576) / 1048576);

        // respect max memory alloc
        if ($size-- > $maxMemoryUsage) {
            $size = $maxMemoryUsage;
        }

        //updating the default value
        \ini_set('memory_limit', $size . 'M');

        $test = \ini_get('memory_limit');
    }
}
