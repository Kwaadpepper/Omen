<?php

namespace Kwaadpepper\Omen\Exceptions;

use Illuminate\Support\Facades\Log;

class OmenHttpException extends OmenException
{
    public function __construct(string $message = "", int $code)
    {
        $this->httpCode = $code;
        parent::__construct($message);
    }

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        if (\round($this->httpCode / 100)) {
            Log::error(sprintf('OMEN <%s> => %s', $this->code, $this->message));
            if ($e = $this->getPrevious())
                Log::debug($this->e);
        }
    }
}
