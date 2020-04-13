<?php

namespace Kwaadpepper\Omen\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class OmenDebugException extends Exception
{

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        Log::debug('!!! ========  OMEN File Manager DEBUG  ======== !!!');
        Log::debug($this->message);
    }
}
