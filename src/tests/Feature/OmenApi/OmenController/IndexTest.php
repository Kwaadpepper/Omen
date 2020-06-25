<?php

namespace Kwaadpepper\Omen\Tests\Feature\OmenApi\ImageController;

use Exception;
use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\OmenHelper;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class IndexTest extends OmenApiTestCase
{
    /** @test */
    public function assertShowIndexSuccess()
    {
        $response = $this->omenApiRequest('GET', route('omenInterface', [], false));
        $response->assertSuccessful();
    }

    /** @test */
    public function assertLocalChangeOnHttpPreferred()
    {
        // Preferred is FR Is translated to FR ?  => FR
        $response = $this->omenApiRequest('GET', route('omenInterface', [], false), [], [
            'HTTP_Accept-Language' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'
        ]);
        $response->assertSuccessful();
        $this->assertTrue(\strpos($response->getContent(), '<html lang="fr">') != false, 'Local change was not set on url');

        // Preferred is EN
        $response = $this->omenApiRequest('GET', route('omenInterface', [], false), [], [
            'HTTP_Accept-Language' => 'en-EN, en;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'
        ]);
        $response->assertSuccessful();
        $this->assertTrue(\strpos($response->getContent(), '<html lang="en">') != false, 'Local change was not set on url');

        // Preferred is JP Is translated to JP ?  => EN
        $response = $this->omenApiRequest('GET', route('omenInterface', [], false), [], [
            'HTTP_Accept-Language' => 'jp-JP, en;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'
        ]);
        $response->assertSuccessful();
        $this->assertTrue(\strpos($response->getContent(), '<html lang="en">') != false, 'Local change was not set on url');
    }

    /** @test */
    public function assertShowIndexWithLocale()
    {
        $response = $this->omenApiRequest('GET', route('omenInterface', [], false), [
            'locale' => 'fr'
        ]);
        $response->assertSuccessful();
        $this->assertTrue(\strpos($response->getContent(), '<html lang="fr">') != false, 'Local change was not set on url');

        $response = $this->omenApiRequest('GET', route('omenInterface', [], false), [
            'locale' => 'en'
        ]);
        $response->assertSuccessful();
        $this->assertTrue(\strpos($response->getContent(), '<html lang="en">') != false, 'Local change was not set on url');

        $response = $this->omenApiRequest('GET', route('omenInterface', [], false), [
            'locale' => 'jp'
        ]);
        $response->assertSuccessful();
        $this->assertTrue(\strpos($response->getContent(), '<html lang="en">') != false, 'Local change was not set on url');
    }

    /** @test */
    public function assertCleanUploadPrivatePathSuccess()
    {
        session()->put('omen.UnitTestForceClean', true);

        $this->createDirectory('tmp', true);
        $this->createFile('tmp/file.txt', true);
        $this->createDirectory('out', true);
        $this->createDirectory('out/dir', true);
        $this->createFile('out/dir/file.txt', true);
        $this->createFile('out/file.txt', true);

        try {
            touch(storage_path(sprintf('app/%s', OmenHelper::privatePath('tmp/file.txt'))), time() - 2 * 24 * 60 * 60);
            touch(storage_path(sprintf('app/%s', OmenHelper::privatePath('out/dir/file.txt'))), time() - 2 * 24 * 60 * 60);
            touch(storage_path(sprintf('app/%s', OmenHelper::privatePath('out/file.txt'))), time() - 2 * 24 * 60 * 60);
        } catch (Exception $e) {
            $this->fail(sprintf('Failed to set file modification time %s', $e));
        }

        $response = $this->omenApiRequest('GET', route('omenInterface', [], false));
        $response->assertSuccessful();

        $this->assertFileDoesNotExist(storage_path(sprintf('app/%s', OmenHelper::privatePath('tmp/file.txt'))));
        $this->assertFileDoesNotExist(storage_path(sprintf('app/%s', OmenHelper::privatePath('out/dir/file.txt'))));
        $this->assertFileDoesNotExist(storage_path(sprintf('app/%s', OmenHelper::privatePath('out/file.txt'))));
        File::deleteDirectory(storage_path(sprintf('app/%s', OmenHelper::privatePath('out'))));
        File::deleteDirectory(storage_path(sprintf('app/%s', OmenHelper::privatePath('tmp'))));
    }
}
