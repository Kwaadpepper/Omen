<?php

namespace Kwaadpepper\Omen\Lib;

use Kwaadpepper\Omen\Exceptions\OmenDebugException;

abstract class InodeFileType
{
    const ARCHIVE = 'archive';
    const VIDEO = 'video';
    const AUDIO = 'audio';
    const IMAGE = 'image';
    const PDF = 'pdf';
    const TEXT = 'text';
    const FILE = 'file';
    const WRITER = 'writer';
    const CALC = 'calc';
    const IMPRESS = 'impress';
    const DISKIMAGE = 'diskimage';
    const EXECUTABLE = 'executable';

    /**
     * Categorize Files by mimeType
     * @param String $mimeType 
     * @return InodeFileType the inode type 
     */
    public static function getFromMimeType(string $mimeType)
    {
        $mimeTypeInfo = self::mimetypeInfo($mimeType);
        switch ($mimeTypeInfo['type']) {
            case 'text':
                return self::TEXT;
            case 'video':
                return self::VIDEO;
            case 'audio':
                return self::AUDIO;
            case 'image':
                return self::IMAGE;
            case 'application':
                switch ($mimeTypeInfo['subtype']) {
                        // https://en.wikipedia.org/wiki/List_of_archive_formats
                    case 'x-bzip':
                    case 'x-bzip2':
                    case 'x-tar':
                    case 'x-rar-compressed':
                    case 'x-7z-compressed':
                    case 'x-lzma':
                    case 'x-lzop':
                    case 'x-xz':
                    case 'x-compress':
                    case 'x-freearc':
                    case 'x-gtar':
                    case 'x-xar':
                    case 'x-zoo':
                    case 'zstd':
                    case 'x-gzip':
                    case 'gzip':
                    case 'zip':
                        return self::ARCHIVE;
                    case 'x-iso9660-image':
                    case 'x-apple-diskimage':
                        return self::DISKIMAGE;
                    case 'octet-stream':
                        return self::FILE;
                    case 'pdf':
                        return self::PDF;
                    case 'xml':
                    case 'json':
                    case 'x-sh':
                    case 'x-perl':
                    case 'x-python':
                    case 'javascript':
                    case 'css':
                        return self::TEXT;
                    case 'rtf':
                    case 'doc':
                    case 'ms-doc':
                    case 'msword':
                    case 'vnd.openxmlformats-officedocument.wordprocessingml.document':
                    case 'vnd.oasis.opendocument.text':
                    case 'vnd.oasis.opendocument.text-template':
                    case 'vnd.sun.xml.writer':
                    case 'vnd.sun.xml.writer.template':
                        return self::WRITER;
                    case 'excel':
                    case 'vnd.ms-excel':
                    case 'x-excel':
                    case 'x-msexcel':
                    case 'vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    case 'vnd.oasis.opendocument.spreadsheet':
                    case 'vnd.oasis.opendocument.spreadsheet-template':
                    case 'vnd.sun.xml.calc':
                    case 'vnd.sun.xml.calc.template':
                        return self::CALC;
                    case 'mspowerpoint':
                    case 'powerpoint':
                    case 'vnd.ms-powerpoint':
                    case 'x-mspowerpoint':
                    case 'vnd.openxmlformats-officedocument.presentationml.presentation':
                    case 'vnd.oasis.opendocument.presentation':
                    case 'vnd.oasis.opendocument.presentation-template':
                    case 'vnd.sun.xml.presentation':
                    case 'vnd.sun.xml.presentation.template':
                        return self::IMPRESS;
                    case 'x-msdownload':
                    case 'x-ms-installer':
                    case 'x-elf':
                        return self::EXECUTABLE;
                        // default:
                        //     return self::FILE;
                }
            default:
                return self::FILE;
        }
    }

    /**
     * Gets mimeType Info
     * https://en.wikipedia.org/wiki/Media_type
     * @param String $mimetype standard mimetype string
     * @return String[] type, subtype, suffix, parameter
     */
    public static function mimetypeInfo(string $mimetype)
    {
        $tmp = \explode('/', $mimetype)[1] ?? '';
        $type = \explode('/', $mimetype)[0] ?? '';
        $subtype = \explode('+', $tmp)[0] ?? '';
        $tmp = \explode('+', $tmp)[1] ?? '';
        $parameter = \explode(';', $tmp)[1] ?? '';
        $suffix = \explode(';', $tmp)[0] ?? '';

        $type = \trim($type);
        $subtype = \trim($subtype);
        $parameter = \trim($parameter);
        $suffix = \trim($suffix);

        return [
            'type' => $type,
            'subtype' => $subtype,
            'suffix' => $suffix,
            'parameter' => $parameter
        ];
    }
}
