<?php

namespace Kwaadpepper\Omen;

use Illuminate\Support\Facades\Config;
use Kwaadpepper\Omen\Exceptions\OmenException;

class OmenHelper
{
    const BYTE_UNITS = ["omen::B", "omen::KB", "omen::MB", "omen::GB", "omen::TB", "omen::PB", "omen::EB", "omen::ZB", "omen::YB"];
    const BYTE_PRECISION = [0, 0, 1, 2, 2, 3, 3, 4, 4];
    const BYTE_NEXT = 1024;

    private static $error;

    public static function errorStoreMessage($e)
    {
        $error = $e;
    }

    public function errorGetMessage()
    {
        return static::$error;
    }

    /**
     * Return the app upload path
     * @param String $path the subfolder in uplod path to point to
     * @return String the full path to upload
     */
    public static function uploadPath(string $path)
    {
        return static::filterPath(config('omen.publicPath'), $path);
    }

    /**
     * Return the app private path
     * @param String $path the subfolder in uplod path to point to
     * @return String the full path to upload
     */
    public static function privatePath(string $path)
    {
        return static::filterPath(config('omen.privatePath'), $path);
    }

    private static function filterPath(string $root, string $path)
    {
        return static::sanitizePath(sprintf('%s/%s', static::mb_rtrim($root, '/'), static::mb_ltrim(static::mb_rtrim($path, '/'), '/')));
    }

    /**
     * Removes unwanted part of string path
     * @param String $path input string path
     * @return String sanitized string path
     */
    public static function sanitizePath(string $path)
    {
        $path = static::preg_replace_all(
            '/\/\.*\//',
            '/',
            $path
        );
        $pathBaseName = static::mb_pathinfo($path, \PATHINFO_BASENAME);
        $path = static::mb_ltrim($path, '.');
        $pathDirname = static::mb_pathinfo($path, \PATHINFO_DIRNAME);

        // $subs = \explode('/', $pathDirname);

        // foreach ($subs as &$folder) {
        //     if (!empty($folder))
        //         $folder = static::filterFilename($folder, true);
        // }
        // $pathDirname = \implode('/', $subs);

        return "{$pathDirname}/{$pathBaseName}";
    }

    /**
     * Filters a file name to support all storages
     * prevent XSS injections and more
     * 
     * https://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
     * 
     * since Laravel depends on mbstring,
     * using mbfonctions is ok
     * 
     * @param String $filename 
     * @param Boolean $beautify 
     * @return String 
     */
    public static function filterFilename(string $filename, bool $beautify = true)
    {
        // sanitize filename
        $filename = \preg_replace(
            '~
            [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
            [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
            [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
            [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
            [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
            ~x',
            '-',
            $filename
        );
        // avoids ".", ".." or ".hiddenFiles"
        $filename = static::mb_ltrim($filename, '.-');
        // optional beautification
        if ($beautify) $filename = static::beautifyFilename($filename);
        // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
        $ext = static::mb_pathinfo($filename, PATHINFO_EXTENSION);
        $filename = \mb_strcut(static::mb_pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? \strlen($ext) + 1 : 0), \mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
        return $filename;
    }

    /**
     * sub function of filterFilename
     * 
     * since Laravel depends on mbstring,
     * using mbfonctions is ok
     * 
     * @param String $filename 
     * @return String 
     */
    private static function beautifyFilename(string $filename)
    {
        // reduce consecutive characters
        $filename = \preg_replace(array(
            // "file   name.zip" becomes "file-name.zip"
            '/ +/',
            // "file___name.zip" becomes "file-name.zip"
            '/_+/',
            // "file---name.zip" becomes "file-name.zip"
            '/-+/'
        ), '-', $filename);
        $filename = \preg_replace(array(
            // "file--.--.-.--name.zip" becomes "file.name.zip"
            '/-*\.-*/',
            // "file...name..zip" becomes "file.name.zip"
            '/\.{2,}/'
        ), '.', $filename);
        // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
        $filename = \mb_strtolower($filename, \mb_detect_encoding($filename));
        // ".file-name.-" becomes "file-name"
        $filename = \trim($filename, '.-');
        return $filename;
    }

    /**
     * Multibyte path info
     * is this needed with php 7 ?
     * 
     * @param String $filepath 
     * @param Int $options
     * [optional]
     * You can specify which elements are returned with optional parameter options. 
     * It composes from PATHINFO_DIRNAME, PATHINFO_BASENAME, PATHINFO_EXTENSION and PATHINFO_FILENAME.
     * It defaults to return all elements.
     * @return String|Array(Strings) 
     */
    public static function mb_pathinfo(string $filepath, int $options = 0)
    {
        $m = $ret = [];
        preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $filepath, $m);

        // copy pointers
        $m[4] = &$m[5];
        $m[8] = &$m[3];

        // remove 0
        unset($m[0]);

        if ($options & 0b1111) {
            return $m[$options & 0b1000] ?? $m[$options & 0b0100] ?? $m[$options & 0b0010] ?? $m[$options & 0b0001] ?? "";
        }

        if ($m[1]) $ret['dirname'] = &$m[1];
        if ($m[2]) $ret['basename'] = &$m[2];
        if ($m[5]) $ret['extension'] = &$m[5];
        if ($m[3]) $ret['filename'] = &$m[3];


        return $ret;
    }

    /**
     * https://stackoverflow.com/questions/10066647/multibyte-trim-in-php
     */
    public static function mb_trim(string $string, $charlist = null)
    {
        if (is_null($charlist)) {
            return trim($string);
        } else {
            $charlist = preg_quote($charlist, '/');
            return preg_replace("/(^[$charlist]+)|([$charlist]+$)/us", '', $string);
        }
    }

    public static function mb_rtrim($string, $charlist = null)
    {
        if (is_null($charlist)) {
            return rtrim($string);
        } else {
            $charlist = preg_quote($charlist, '/');
            return preg_replace("/([$charlist]+$)/us", '', $string);
        }
    }

    public static function mb_ltrim($string, $charlist = null)
    {
        if (is_null($charlist)) {
            return ltrim($string);
        } else {
            $charlist = preg_quote($charlist, '/');
            return preg_replace("/(^[$charlist]+)/us", '', $string);
        }
    }

    public static function getAllowedFilesExtensions()
    {
        $out = [];
        $allowed = config('omen.autorizedUploadExtensions');
        $disallowed = config('omen.deniedUploadExtensions');

        \array_walk_recursive($allowed, function ($value, $key) use (&$out) {
            if (!is_array($value)) $out[$value] = $value;
        });

        \array_walk_recursive($disallowed, function ($value, $key) use (&$out) {
            if (!is_array($value)) unset($out[$value]);
        });

        return $out;
    }

    /**
     * Preg replace recursive
     * @param String|Array $pattern
     * @param String|Array $replacement 
     * @param String|Array $subject 
     * @param Int $limit 
     * @param Mixed $count 
     * @return String|Array|Null recursive replaced $subject
     */
    private static function preg_replace_all($pattern, $replacement, string $subject, int $limit = -1, &$count = 0)
    {
        $privateLimit = 40;
        $count = $subcount = 0;
        do {
            $subject = \preg_replace(
                $pattern,
                $replacement,
                $subject,
                $limit,
                $subcount
            );
            $count += $subcount;
        } while ($subcount and $privateLimit--);

        return $subject;
    }

    public static function formatCspReport($cspReport)
    {
        return \sprintf(
            '<warning>%s</warning> on %s blocked <error>%s</error>  using policy  <question>%s</question>  => referer %s',
            $cspReport['csp-report']['violated-directive'],
            $cspReport['csp-report']['document-uri'],
            $cspReport['csp-report']['blocked-uri'],
            $cspReport['csp-report']['original-policy'],
            $cspReport['csp-report']['referrer'],
        );
    }

    /**
     * https://gist.github.com/liunian/9338301
     * 
     * Convert bytes to be human readable.
     *
     * @param Integer      $bytes     Bytes to make readable
     * @param Integer|Null $precision Precision of rounding
     *
     * @return String Human readable bytes
     */
    public static function HumanReadableBytes($bytes, $precision = null)
    {
        for ($i = 0; ($bytes / self::BYTE_NEXT) >= 0.9 && $i < count(self::BYTE_UNITS); $i++) $bytes /= self::BYTE_NEXT;
        return round($bytes, is_null($precision) ? self::BYTE_PRECISION[$i] : $precision) . __(self::BYTE_UNITS[$i]);
    }

    /**
     * Assert a variable's value
     * @param mixed $variable The variable to test
     * @param mixed $value The value it should have
     * @return void 
     * @throws OmenException if assertion is wrong
     */
    public static function assert($variable, $value)
    {
        if ($variable != $value) {
            $parentClass = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? '';
            $parentFunction = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? '';
            $parentLine = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'] ?? '';
            throw new OmenException("Asserted $variable was equal to $value in $parentClass $parentFunction on line $parentLine");
        };
    }

    /**
     * Assert type of variable
     * (Supports class testing)
     * @param Mixed $variable Any variable to test
     * @param String $type A string with the type of $variable to assert (case insensitive)
     * @return Void
     * @throws OmenException when $variable does not match $type or is a ressource (unsupported), or the type is unknown
     */
    public static function assertType($variable, string $type)
    {
        $varType = \gettype($variable);
        $check = false;
        $getContext = function () {
            return [
                debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'] ?? '',
                debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'] ?? '',
                debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['line'] ?? ''
            ];
        };
        switch ($varType) {
            case 'integer':
            case 'float':
            case 'double':
                $check = \in_array(\strtolower($type), ['integer', 'float', 'double']);
                break;
            case 'boolean':
            case 'string':
            case 'array':
                $check = $varType == \strtolower($type);
                break;
            case 'NULL':
                $check = \isNull($type) ? true : (
                    (\strtolower($type) == 'null') ? true : false);
                break;
            case 'resource':
            case 'resource (closed)':
                list($parentClass, $parentFunction, $parentLine) = $getContext();
                throw new OmenException("Unupported $variable type $varType assertion in $parentClass $parentFunction on line $parentLine");
            case 'unknown type':
                list($parentClass, $parentFunction, $parentLine) = $getContext();
                throw new OmenException("Unupported $variable type $varType assertion in $parentClass $parentFunction on line $parentLine");
            case 'object':
                if (\strtolower($type) == \get_class($variable)) {
                    $check = true;
                }
        }

        if (!$check) {
            list($parentClass, $parentFunction, $parentLine) = $getContext();
            throw new OmenException("Asserted $variable was type of $type in $parentClass $parentFunction on line $parentLine");
        };
    }

    public static function abort(int $code, string $message = '')
    {
        if ($message == '') {
            switch ($code) {
                case 400:
                    $message = __('Bad request');
                    break;
                case 404:
                    $message = __('File not found');
                    break;
                case 409:
                    $message = __('Page expired');
                    break;
                case 500:
                    $message = __('Server error');
                    break;
            }
        }
        return (new OmenException($message, $code))->render(\request(), true);
    }
}
