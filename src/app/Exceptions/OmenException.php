<?php

namespace Kwaadpepper\Omen\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Kwaadpepper\Omen\OmenHelper;
use Throwable;

class OmenException extends Exception
{

    protected $isConsole = false;
    protected $httpCode = 500;

    public function __construct(string $message = "", Throwable $previous = null, bool $isConsole = false)
    {
        if ($isConsole) {
            $this->isConsole = true;
        }
        parent::__construct($message, 0, $previous);
        $this->code = $this->genCode();
    }

    /**
     * Generated an error code
     * containing path tip of the involved file
     * and the throwed line
     * @return string Base64 encode string without padding
     */
    private function genCode()
    {
        $lvls = \mb_split('/', OmenHelper::mb_ltrim($this->file, $this->omenAppPath()));
        $lPath = '';
        foreach ($lvls as $k => $lvl) {
            $lvl = \ltrim($lvl, '.php');
            if (\count($lvls) - 1 == $k) {
                $lPath .= \sprintf('%s', \substr($lvl, 0, 5 + \strlen($lPath . $this->line) % 3));
            } else {
                $lPath .= \sprintf('%s', \substr($lvl, 0, 3));
            }
        }
        return \base64_encode($lPath . $this->line);
    }

    /**
     * Get the app relative path
     * @return string 
     */
    private function omenAppPath()
    {
        $t = \mb_split('/', __FILE__);
        return \implode('/', array_slice($t, 0, \count($t) - 3));
    }

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        // If error code starts by '8' then this is an console Error
        if ($this->isConsole) {
            $this->renderForConsole();
        }
        Log::error(sprintf('OMEN <%s> => %s', $this->code, $this->message));

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
        if ($request->ajax()) {
            return response()->json([
                'code' => $this->code,
                'message' => $this->message
            ], $this->httpCode);
        }
        return response()->view('omen::tools.exception', [
            'code' => $this->code,
            'message' => $this->message
        ], $this->httpCode);
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

    protected function questionLine(string $line)
    {
        return "<question>$line</question>";
    }

    protected function commentLine(string $line)
    {
        return "<comment>$line</comment>";
    }

    protected function infoLine(string $line)
    {
        return "<info>$line</info>";
    }

    protected function errorLine(string $line)
    {
        return "<error>$line</error>";
    }
}
