<?php

namespace Kwaadpepper\Omen;

use Error;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Kwaadpepper\Omen\Exceptions\OmenDebugException;
use Kwaadpepper\Omen\Exceptions\OmenHttpException;
use TypeError;

class OmenHelper
{
    const BYTE_UNITS = ["omen::B", "omen::KB", "omen::MB", "omen::GB", "omen::TB", "omen::PB", "omen::EB", "omen::ZB", "omen::YB"];
    const BYTE_PRECISION = [0, 0, 1, 2, 2, 3, 3, 4, 4];
    const BYTE_NEXT = 1024;

    /**
     * Return the app upload path
     * @param String $path the subfolder in uplod path to point to
     * @return String the full path to upload
     */
    public static function uploadPath(string $path = '')
    {
        return static::filterPath(static::mb_ltrim(config('omen.publicPath'), '/'), $path);
    }

    /**
     * Return the app private path
     * @param String $path the subfolder in uplod path to point to
     * @return String the full path to upload
     */
    public static function privatePath(string $path = '')
    {
        return static::filterPath(static::mb_ltrim(config('omen.privatePath'), '/'), $path);
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
            '/\/{2,}/',
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

        return sprintf(
            '%s%s%s',
            $pathDirname,
            (\strlen($pathDirname) && \strlen($pathBaseName)) ||
                (!\strlen($pathDirname) && \strlen($pathBaseName) && \strpos($path, '/') == 0) ? '/' : '',
            $pathBaseName
        );
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
     * mimics pathinfo php function
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
        // if PATHINFO_DIRNAME and '/' or '/file' or '/file.jpg'
        if (preg_match('%^\/[^\/.]*?\.[^\/.]*?$|^\/[^\/.]*?$|^\/$%im', $filepath) == 1 and $options == 1) {
            return '/';
        }
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

        if ($filepath == '/') {
            return ['dirname' => '/'];
        }

        if ($m[1]) $ret['dirname'] = &$m[1];
        if ($m[2]) $ret['basename'] = &$m[2];
        if ($m[5]) $ret['extension'] = &$m[5];
        if ($m[3]) $ret['filename'] = &$m[3];

        if (preg_match('%^\/[^\/.]*?\.[^\/.]*?$|^\/[^\/.]*?$|^\/$%im', $filepath) == 1) {
            $ret['dirname'] = '/';
        }

        return $ret;
    }

    /**
     * https://stackoverflow.com/questions/10066647/multibyte-trim-in-php
     */
    public static function mb_trim($string, $charlist = null)
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

    public static function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    /**
     * @return array 
     */
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
    public static function preg_replace_all($pattern, $replacement, string $subject, int $limit = -1, &$count = 0)
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
     * @throws OmenDebugException if assertion is wrong
     */
    public static function assert($a, $b)
    {
        $aType = \gettype($a);
        $bType = \gettype($b);
        $aString = \gettype($a) == 'array' ? 'array' : (\gettype($a) == 'object' and
            !\method_exists($a, 'toString') ? 'object' : \strval($a));
        $bString = \gettype($b) == 'array' ? 'array' : (\gettype($b) == 'object' and
            !\method_exists($b, 'toString') ? 'object' : \strval($b));
        $isEqual = false;
        if ($aType == 'array' and $bType == 'array') {
            $isEqual = !\count(\array_diff($a, $b));
        } else if ($aType == 'object' and $bType == 'object') {
            try {
                if (\method_exists($a, 'isEqualTo')) {
                    $isEqual = $a->isEqualTo($b);
                } else if (\method_exists($b, 'isEqualTo')) {
                    $isEqual = $b->isEqualTo($a);
                } else {
                    $isEqual = $a === $b;
                }
            } catch (Error | TypeError $e) {
                $isEqual = $a == $b;
            }
        } else {
            $isEqual = $a == $b;
        }
        if ($aType == 'object') {
            $aType = get_class($a);
        }
        if ($bType == 'object') {
            $bType = get_class($b);
        }
        if (!$isEqual) {
            $parentClass = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? '';
            $parentFunction = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? '';
            $parentLine = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'] ?? '';
            throw new OmenDebugException(\sprintf(
                "Asserted %s:%s was equal to %s:%s in %s %s on line %s",
                \strval($aType),
                \strval($aString),
                \strval($bType),
                \strval($bString),
                \strval($parentClass),
                \strval($parentFunction),
                \strval($parentLine)
            ));
        }
    }

    /**
     * Assert type of variable
     * (Supports class testing)
     * @param Mixed $variable Any variable to test
     * @param String $type A string with the type of $variable to assert (case insensitive)
     * @return Void
     * @throws OmenDebugException when $variable does not match $type or is a ressource (unsupported), or the type is unknown
     */
    public static function assertType($variable, string $type)
    {
        $varType = \strtolower(\gettype($variable));
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
            case 'null':
                $check = \strtolower($type) == $varType;
                break;
            case 'resource':
            case 'resource (closed)':
            case 'unknown type':
                list($parentClass, $parentFunction, $parentLine) = $getContext();
                throw new OmenDebugException("Unupported $variable type $varType assertion in $parentClass $parentFunction on line $parentLine");
            case 'object':
                $check = \strtolower($type) == \strtolower(\get_class($variable));
        }

        if (!$check) {
            list($parentClass, $parentFunction, $parentLine) = $getContext();
            throw new OmenDebugException("Asserted $variable was type of $type in $parentClass $parentFunction on line $parentLine");
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
        return (new OmenHttpException($message, $code))->render(\request(), true);
    }
}
