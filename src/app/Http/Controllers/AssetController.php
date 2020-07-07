<?php

namespace Kwaadpepper\Omen\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Kwaadpepper\Omen\OmenHelper;

class AssetController extends Controller
{
    /**
     * Serve asset if not published
     */
    public function asset(Request $request, string $fileUri)
    {
        try {
            $filePath = __DIR__ . '/../../../../resources/' . $fileUri;
            $fileContent = File::get($filePath);
            $fileExt = OmenHelper::mb_pathinfo($filePath, PATHINFO_EXTENSION);

            //fix for missing mimetypes in https://github.com/guzzle/psr7/blob/master/src/functions.php
            // https://www.iana.org/assignments/media-types/media-types.xhtml
            $mimeFix = [
                'woff2' => 'application/font-woff2',
                'woff' => 'application/font-woff',
                'ttf' => 'application/font-ttf',
                'sfnt' => 'application/font-sfnt',
                'otf' => 'application/font-otf',
                'collection' => 'application/font-collection'
            ];

            $mimeType = $mimeFix[$fileExt] ?? MimeType::from($filePath);
            $fileSize = File::size($filePath);
            return response($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Size' => $fileSize
            ]);
        } catch (FileNotFoundException $e) {
            return OmenHelper::abort(404);
        }
    }

    public function browserconfig()
    {
        return response(view('omen::elements.assets.browserconfig')->render(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function webmanifest()
    {
        return response(view('omen::elements.assets.webmanifest')->render(), 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
