<?php

namespace Kwaadpepper\Omen\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class OmenLinkStorage extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omen:link {--relative : Create the symbolic link using relative paths}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a link of the omen upload storage to the public folder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $link = storage_path(sprintf('app/public/%s', config('omen.publicPath')));
        $target = storage_path(sprintf('app/%s', config('omen.publicPath')));

        if (\file_exists($link)) {
            $this->error("The [$link] link already exists.");
            exit;
        } else {
            if ($this->option('relative')) {
                $target = $this->getRelativeTarget($link, $target);
            }

            if (!\file_exists(\dirname($link))) {
                $this->createParentFolder($link);
            }

            $this->laravel->make('files')->link($target, $link);

            $this->info("The [$link] link has been connected to [$target].");
        }

        // Do the storage link in public folder
        $this->call('storage:link');
    }

    /**
     * Recursive create parent folder => mkdir -p
     * @param String $path
     */
    protected function createParentFolder($path)
    {
        $directory = \dirname($path);
        $parent = \dirname($directory);

        if (!\file_exists($parent)) {
            $this->createParentFolder($directory);
        }

        $diretoryCreated = false;

        try {
            $diretoryCreated = File::makeDirectory($directory);
        } catch (Exception $e) {
            throw new OmenException(\sprintf('Error during Folder creation %s => %s', $directory, $e->getMessage()), null, true);
        }

        if (!$diretoryCreated) {
            throw new OmenException(\sprintf('Can\'t create folder %s unknown reason', $directory), null, true);
        }

        $this->info(\sprintf('created folder %s', $directory));
    }

    /**
     * Get the relative path to the target.
     *
     * @param  String  $link
     * @param  String  $target
     * @return String
     * @throws OmenException if missing SymfonyFilesystem::class
     */
    protected function getRelativeTarget($link, $target)
    {
        if (!class_exists(SymfonyFilesystem::class)) {
            throw new OmenException('Please install the symfony/filesystem Composer package to create relative links.', null, true);
        }

        return (new SymfonyFilesystem)->makePathRelative($target, \dirname($link));
    }
}
