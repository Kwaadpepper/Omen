<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\UploadController;

use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class UploadTest extends OmenApiTestCase
{
    /** @test */
    public function uploadSmallFileSuccess()
    {
        list($fileSize, $chunks) = $this->getFileChunks(1);
        $response = $this->omenApiUpload(
            storage_path(sprintf('app/%s', OmenHelper::privatePath())),
            '/',
            'fileTest.txt',
            $fileSize,
            23,
            1,
            1,
            $chunks[0]['content']
        );

        $response->assertOk();
        $this->assertFileExists(storage_path(sprintf('app/%s', OmenHelper::uploadPath('filetest.txt'))));
    }

    /** @test */
    public function uploadSmallFileSuccessIfFileAlreadyExists()
    {
        list($fileSize, $chunks) = $this->getFileChunks(1);
        $response = $this->omenApiUpload(
            storage_path(sprintf('app/%s', OmenHelper::privatePath())),
            '/',
            'fileTest.txt',
            $fileSize,
            23,
            1,
            1,
            $chunks[0]['content']
        );
        $response->assertOk();
        $this->assertFileExists(storage_path(sprintf('app/%s', OmenHelper::uploadPath('filetest1.txt'))));
    }

    /** @test */
    public function uploadFailBecauseFileNameIsTooShort()
    {
        $fP = storage_path(sprintf('app/%s', OmenHelper::privatePath()));
        $fPUp = storage_path(sprintf('app/%s', OmenHelper::uploadPath('fi.txt')));
        list($fileSize, $chunks) = $this->getFileChunks(1);
        $response = $this->omenApiUpload($fP, '/', 'fi.txt', $fileSize, 23, 1, 1, $chunks[0]['content']);
        $response->assertStatus(400);
        $response->assertJsonStructure(['chunkIndex', 'error']);
        $response->assertJsonFragment([
            'chunkIndex' => 1
        ]);
        File::delete(sprintf('%s/%s', $fP, 'fi.txt'));
        $this->assertFileDoesNotExist(sprintf('%s/%s', $fP, 'fi.txt'));
        $this->assertFileDoesNotExist($fPUp);
    }

    /** @test */
    public function uploadFailBecauseFileIsTooLarge()
    {
        $conf = config('omen.maxUploadSize');
        config(['omen.maxUploadSize' => '1M']);
        $fP = storage_path(sprintf('app/%s', OmenHelper::privatePath()));
        $fPUp = storage_path(sprintf('app/%s', OmenHelper::uploadPath('fi.txt')));
        list($fileSize, $chunks) = $this->getFileChunks(1);
        $response = $this->omenApiUpload($fP, '/', 'fi.txt', $fileSize, 23, 1, 1, $chunks[0]['content']);
        $response->assertStatus(400);
        $response->assertJsonStructure(['chunkIndex', 'error']);
        $response->assertJsonFragment([
            'chunkIndex' => 1
        ]);
        File::delete(sprintf('%s/%s', $fP, 'fi.txt'));
        $this->assertFileDoesNotExist(sprintf('%s/%s', $fP, 'fi.txt'));
        $this->assertFileDoesNotExist($fPUp);
        config(['omen.maxUploadSize' => $conf]);
    }

    /** @test */
    public function uploadFailBecauseRealFileIsTooLarge()
    {
        $conf = config('omen.maxUploadSize');
        config(['omen.maxUploadSize' => '1M']);
        $fP = storage_path(sprintf('app/%s', OmenHelper::privatePath()));
        list($fileSize, $chunks) = $this->getFileChunks(1);
        $response = $this->omenApiUpload($fP, '/', 'file.txt', 200, 23, 1, 1, $chunks[0]['content']);
        $response->assertStatus(400);
        $response->assertJsonStructure(['chunkIndex', 'error']);
        $response->assertJsonFragment([
            'chunkIndex' => 1
        ]);
        $this->assertFileDoesNotExist(sprintf('%s/%s', $fP, 'file.txt'));
        config(['omen.maxUploadSize' => $conf]);
    }

    /** @test */
    public function uploadFailBecauseRealFileIsDifferentThanAnnounced()
    {
        $conf = config('omen.maxUploadSize');
        config(['omen.maxUploadSize' => '10M']);
        $fP = storage_path(sprintf('app/%s', OmenHelper::privatePath()));
        $fPUp = storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.txt')));
        list($fileSize, $chunks) = $this->getFileChunks(1);
        $response = $this->omenApiUpload($fP, '/', 'file.txt', 200, 23, 1, 1, $chunks[0]['content']);
        $response->assertStatus(400);
        $response->assertJsonStructure(['chunkIndex', 'error']);
        $response->assertJsonFragment([
            'chunkIndex' => 1
        ]);
        $this->assertFileDoesNotExist($fPUp);
        config(['omen.maxUploadSize' => $conf]);
    }

    /** @test */
    public function uploadFailBecauseRealFileIsDifferentThanMimeTypeFromFileExt()
    {
        $conf = config('omen.maxUploadSize');
        config(['omen.maxUploadSize' => '10M']);
        $imageContent = File::get(__DIR__ . '/../../../resources/image.png');
        $fP = storage_path(sprintf('app/%s', OmenHelper::privatePath()));
        $fPUp = storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.txt')));
        $response = $this->omenApiUpload($fP, '/', 'file.txt', \strlen($imageContent), 23, 1, 1, $imageContent);
        $response->assertStatus(400);
        $response->assertJsonStructure(['chunkIndex', 'error']);
        $response->assertJsonFragment([
            'chunkIndex' => 1
        ]);
        $this->assertFileDoesNotExist($fPUp);
        config(['omen.maxUploadSize' => $conf]);
    }

    /** @test */
    public function uploadFailBecauseRealFileMimeTypeIsNotAllowed()
    {
        $conf = config('omen.maxUploadSize');
        config(['omen.maxUploadSize' => '10M']);
        config(['omen.deniedUploadExtensions' => ['txt']]);
        $fP = storage_path(sprintf('app/%s', OmenHelper::privatePath()));
        $fPUp = storage_path(sprintf('app/%s', OmenHelper::uploadPath('file.txt')));
        list($fileSize, $chunks) = $this->getFileChunks(1);
        $response = $this->omenApiUpload($fP, '/', 'file.txt', $fileSize, 23, 1, 1, $chunks[0]['content']);
        $response->assertStatus(400);
        $response->assertJsonStructure(['chunkIndex', 'error']);
        $response->assertJsonFragment([
            'chunkIndex' => 1
        ]);
        $this->assertFileDoesNotExist($fPUp);
        config(['omen.maxUploadSize' => $conf]);
        config(['omen.deniedUploadExtensions' => []]);
    }
}
