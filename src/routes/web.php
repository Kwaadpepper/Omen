<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' => ['OmenMiddleware'],
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers'
], function () {
    $routePrefix = config('omen.urlPrefix');

    Route::match(['get'],  sprintf('%s/', $routePrefix), 'OmenController@index')->name('omenInterface');

    // JS Download
    Route::match(['get'], sprintf('%s/download/{file}', $routePrefix), 'OmenController@download')->where('file', '(.*)')->name('omenDownload');

    // Assets
    Route::get(sprintf('%s/{fileUri}', config('omen.assetPath')), 'OmenController@asset')->where('fileUri', '.*')->name('omenAsset');
});
