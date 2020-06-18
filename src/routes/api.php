<?php

use Illuminate\Support\Facades\Route;

$middlewareMinimal = include(__DIR__ . '/middlewareMinimal.php');

Route::group([
    'as' => 'OmenApi.',
    'middleware' => array_merge(
        ['omenthrottle:100,1'],
        $middlewareMinimal,
        [\Kwaadpepper\Omen\Http\Middleware\OmenApiCSRFMiddleware::class]
    ),
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers'
], function () {
    $routePrefix = config('omen.urlPrefix');

    // File Upload
    Route::match(['post'], sprintf('%s/upload', $routePrefix), 'UploadController@upload')->name('omenUpload');

    // Inode copy
    Route::match(['post'], sprintf('%s/copyto', $routePrefix), 'CrudController@copyto')->name('omenCopyto');

    // Inode move
    Route::match(['post'], sprintf('%s/moveto', $routePrefix), 'CrudController@moveto')->name('omenMoveto');

    // File Rename
    Route::match(['post'], sprintf('%s/rename', $routePrefix), 'CrudController@rename')->name('omenRename');

    // File Delete
    Route::match(['post'], sprintf('%s/delete', $routePrefix), 'CrudController@delete')->name('omenDelete');

    // File create text file
    Route::match(['post'], sprintf('%s/createtextfile', $routePrefix), 'TextController@createTextFile')->name('omenCreateTextFile');

    // File update text file
    Route::match(['post'], sprintf('%s/updatetextfile', $routePrefix), 'TextController@updateTextFile')->name('omenUpdateTextFile');

    // File create directory
    Route::match(['post'], sprintf('%s/createdirectory', $routePrefix), 'CrudController@createDirectory')->name('omenCreateDirectory');

    // Image resize
    Route::match(['post'], sprintf('%s/resizeimage', $routePrefix), 'ImageController@resize')->name('omenResizeImage');

    // Image crop
    Route::match(['post'], sprintf('%s/cropimage', $routePrefix), 'ImageController@crop')->name('omenCropImage');

    // File get Inode Html
    Route::match(['get'], sprintf('%s/getinodehtml', $routePrefix), 'OutputController@getInodeHtml')->name('omenGetInodeHtml');

    // get All Inodes at path
    Route::match(['get'], sprintf('%s/getinodesatpath', $routePrefix), 'OutputController@getInodesAtPath')->name('omenGetInodesAtPath');

    // get breacrumb at path
    Route::match(['get'], sprintf('%s/getbreadcrumbatpath', $routePrefix), 'OutputController@getBreadcrumbAtPath')->name('omenGetBreadcrumbAtPath');

    // JS ping, is used to check session validity
    Route::match(['post'], sprintf('%s/ping', $routePrefix), 'ServiceController@ping')->name('omenPing');

    // JS LOG
    Route::match(['post'], sprintf('%s/log', $routePrefix), 'ServiceController@log')->name('omenLog');
});

// public route
Route::group([
    'as' => 'OmenReport.',
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers',
    'middleware' => array_merge(['omenthrottle:100,1'], $middlewareMinimal)
], function () {
    Route::match(['post'], sprintf('%s/csp/report', config('omen.urlPrefix')), 'ServiceController@cspReport')->name('omenCspReport');
});
