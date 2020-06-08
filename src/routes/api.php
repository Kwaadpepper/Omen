<?php

use Illuminate\Support\Facades\Route;

$middlewareMinimal = include(__DIR__ . '/middlewareMinimal.php');

Route::group([
    'middleware' => array_merge(
        ['throttle:100,1'],
        $middlewareMinimal,
        [\Kwaadpepper\Omen\Http\Middleware\OmenApiCSRFMiddleware::class]
    ),
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers'
], function () {
    $routePrefix = config('omen.urlPrefix');

    // File Upload
    Route::match(['post'], sprintf('%s/upload', $routePrefix), 'UploadController@upload')->name('omenUpload');

    // File Rename
    Route::match(['post'], sprintf('%s/rename', $routePrefix), 'OmenController@rename')->name('omenRename');

    // File Delete
    Route::match(['post'], sprintf('%s/delete', $routePrefix), 'OmenController@delete')->name('omenDelete');

    // Inode copy
    Route::match(['post'], sprintf('%s/copyto', $routePrefix), 'OmenController@copyto')->name('omenCopyto');

    // Inode move
    Route::match(['post'], sprintf('%s/moveto', $routePrefix), 'OmenController@moveto')->name('omenMoveto');

    // File create text file
    Route::match(['post'], sprintf('%s/createtextfile', $routePrefix), 'TextController@createTextFile')->name('omenCreateTextFile');

    // File create text file
    Route::match(['post'], sprintf('%s/updatetextfile', $routePrefix), 'TextController@updateTextFile')->name('omenUpdateTextFile');

    // File create text file
    Route::match(['post'], sprintf('%s/createdirectory', $routePrefix), 'OmenController@createDirectory')->name('omenCreateDirectory');

    // Image resize
    Route::match(['post'], sprintf('%s/resizeimage', $routePrefix), 'ImageController@resize')->name('omenResizeImage');

    // Image crop
    Route::match(['post'], sprintf('%s/cropimage', $routePrefix), 'ImageController@crop')->name('omenCropImage');

    // File get Inode Html
    Route::match(['get'], sprintf('%s/getinodehtml', $routePrefix), 'OmenController@getInodeHtml')->name('omenGetInodeHtml');

    // get All Inodes at path
    Route::match(['get'], sprintf('%s/getinodesatpath', $routePrefix), 'OmenController@getInodesAtPath')->name('omenGetInodesAtPath');

    // get breacrumb at path
    Route::match(['get'], sprintf('%s/getbreadcrumbatpath', $routePrefix), 'OmenController@getBreadcrumbAtPath')->name('omenGetBreadcrumbAtPath');

    // JS ping, is used to check session validity
    Route::match(['post'], sprintf('%s/ping', $routePrefix), 'OmenController@ping')->name('omenPing');

    // JS LOG
    Route::match(['post'], sprintf('%s/log', $routePrefix), 'OmenController@log')->name('omenLog');
});

// public route
Route::group([
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers',
    'middleware' => array_merge(['throttle:100,1'], $middlewareMinimal)
], function () {
    Route::match(['post'], sprintf('%s/csp/report', config('omen.urlPrefix')), 'OmenController@cspReport')->name('omenCspReport');
});
