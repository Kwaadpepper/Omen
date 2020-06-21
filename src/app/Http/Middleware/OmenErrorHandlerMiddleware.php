<?php

namespace Kwaadpepper\Omen\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Kwaadpepper\Omen\Exceptions\OmenException;
use Throwable;

class OmenErrorHandlerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        set_error_handler($this->OmenErrorHandler($request));
        return $next($request);
    }

    private function OmenErrorHandler($request)
    {
        return function (int $errno, string $errstr, string $errfile, int $errline) use ($request) {
            Log::critical(sprintf('Omen: Fatal error (%d) => %s line %d in %s', $errno, $errstr, $errline, $errfile));
            $this->printDebugBacktrace();
            (new OmenException('An unknow error happened in Omen'))->render($request)->send();
            die();
        };
    }

    private function printDebugBacktrace()
    {
        $trace = debug_backtrace();
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);

        for ($i = 0; $i < $length; $i++) {
            $t = $trace[$i];
            Log::debug(\sprintf(
                '%d) %s%s line %d => %s',
                $i,
                \array_key_exists('class', $t) ? \sprintf('%s::', $t['class']) : '',
                $t['function'] ?? '',
                $t['line'] ?? 0,
                $t['file'] ?? ''
            ));
        }
    }
}
