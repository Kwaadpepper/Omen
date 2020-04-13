<?php

namespace Kwaadpepper\Omen\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class OmenException extends Exception
{

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        // If error code starts by '8' then this is an console Error
        if (\substr($this->code, 0, 1) == '8') {
            $this->renderForConsole();
        }
        Log::error('!!! ========  OMEN File Manager ERROR  ======== !!!');
        Log::error(sprintf('N%d => %s', $this->code, $this->message));

        if ($e = $this->getPrevious())
            Log::debug($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->view('omen::tools.exception', [
            'code' => $this->code . $this->line,
            'message' => $this->message
        ], 500);
    }

    private function renderForConsole()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln('');
        $out->writeln($this->errorLine('OMEN File Manager ERROR    '));
        $out->writeln($this->infoLine('  Please check logs for debug'));
        $out->writeln($this->commentLine(sprintf('  Code : %d   %s', $this->code, $this->message)));
        $out->writeln('');
        if ($e = $this->getPrevious())
            Log::debug($e);
        exit;
    }

    private function questionLine(string $line)
    {
        return "<question>$line</question>";
    }

    private function commentLine(string $line)
    {
        return "<comment>$line</comment>";
    }

    private function infoLine(string $line)
    {
        return "<info>$line</info>";
    }

    private function errorLine(string $line)
    {
        return "<error>$line</error>";
    }
}
