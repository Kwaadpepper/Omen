<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' => ['OmenMiddleware', 'throttle:100,1'],
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers'
], function () {
    $routePrefix = config('omen.urlPrefix');

    // File Upload
    Route::match(['post'], sprintf('%s/upload', $routePrefix), 'OmenController@upload')->name('omenUpload');

    // File Rename
    Route::match(['post'], sprintf('%s/rename', $routePrefix), 'OmenController@rename')->name('omenRename');

    // File Delete
    Route::match(['post'], sprintf('%s/delete', $routePrefix), 'OmenController@delete')->name('omenDelete');

    // File create text file
    Route::match(['post'], sprintf('%s/createtextfile', $routePrefix), 'OmenController@createTextFile')->name('omenCreateTextFile');

    // File create text file
    Route::match(['post'], sprintf('%s/createdirectory', $routePrefix), 'OmenController@createDirectory')->name('omenCreateDirectory');

    // File get Inode Html
    Route::match(['get'], sprintf('%s/getinodehtml', $routePrefix), 'OmenController@getInodeHtml')->name('omenGetInodeHtml');

    // JS LOG
    Route::match(['post'], sprintf('%s/log', $routePrefix), 'OmenController@log')->name('omenLog');
});

// public route
Route::group([
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers',
    // 'middleware' => 'throttle:10,1'
], function () {
    Route::match(['post'], sprintf('%s/csp/report', config('omen.urlPrefix')), 'OmenController@cspReport')->name('omenCspReport');
});
