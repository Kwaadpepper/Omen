<?php

namespace Kwaadpepper\Omen\Lib;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\File;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as ExceptionFileNotFoundException;

/**
 * Gets an Inode to a local File with a temp
 * name.
 * @package Kwaadpepper\Omen\Lib
 * @author Jérémy Munsch
 */
class LocalFile extends File
{
    private $handle;
    /**
     * Build the temp file
     * @param Inode $inode The inode to get file from
     * @return void 
     * @throws OmenException if inode is a directory
     * @throws FileNotFoundException if the inode got not be retrieved
     * @throws ExceptionFileNotFoundException If the given path is not a file (should not be throwned)
     */
    public function __construct(Inode $inode)
    {
        $this->handle = tmpfile();
        \fwrite($this->handle, $inode->get());
        if (!$this->handle) {
            throw new OmenException('Could not create a temp file', "34" + __LINE__);
        }
        $metaDatas = stream_get_meta_data($this->handle);
        parent::__construct($metaDatas['uri'], true);
    }

    /**
     * Release the temp file so it can be destroyed at
     * any time to release memory
     * @return void 
     */
    public function release()
    {
        \fclose($this->handle);
    }
}
