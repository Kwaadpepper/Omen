<?php

namespace Kwaadpepper\Omen\Test\Feature\OmenConsole;

use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\Tests\Feature\OmenApi\OmenApiTestCase;

class LinkStorageTest extends OmenApiTestCase
{
    /** @test */
    public function link()
    {
        $c = $this->artisan('omen:link')->assertExitCode(0);
        $this->assertDirectoryExists(public_path('storage/omen/uploads'));
        $this->assertDirectoryExists(storage_path('app/public/omen/uploads'));
        File::deleteDirectory(storage_path('app/public'));
        File::delete(public_path('storage'));
        $this->assertDirectoryDoesNotExist(storage_path('app/public/omen/uploads'));
        $this->assertFileDoesNotExist(public_path('storage'));
    }

    /** @test */
    public function linkRelative()
    {
        $c = $this->artisan('omen:link --relative')->assertExitCode(0);
        $this->assertDirectoryExists(public_path('storage/omen/uploads'));
        $this->assertDirectoryExists(storage_path('app/public/omen/uploads'));
        File::deleteDirectory(storage_path('app/public'));
        File::delete(public_path('storage'));

        $this->assertDirectoryDoesNotExist(public_path('storage/omen/uploads'));
        $this->assertDirectoryDoesNotExist(storage_path('app/public/omen/uploads'));
    }
}
