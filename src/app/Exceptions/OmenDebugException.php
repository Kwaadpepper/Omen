<?php

namespace Kwaadpepper\Omen\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class OmenDebugException extends OmenException
{

    public function report()
    {
        // If error code starts by '8' then this is an console Error
        if ($this->isConsole) {
            $this->renderForConsole();
        }
        Log::error('!!! ========  OMEN File Manager DEBUG  ======== !!!');
        Log::error(sprintf('N%d => %s', $this->code, $this->message));

        if ($e = $this->getPrevious())
            Log::debug($e);
    }

    private function renderForConsole()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln('');
        $out->writeln($this->infoLine('OMEN File Manager DEBUG    '));
        $out->writeln($this->commentLine(sprintf('  Code : %d   %s', $this->code, $this->message)));
        $out->writeln('');
        if ($e = $this->getPrevious())
            Log::debug($e);
        exit;
    }
}
