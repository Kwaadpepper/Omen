<?php

use Illuminate\Support\Facades\Route;

$middlewareMinimal = include(__DIR__ . '/middlewareMinimal.php');

Route::group([
    'middleware' => array_merge(['throttle:60,1'], $middlewareMinimal, [\App\Http\Middleware\VerifyCsrfToken::class]),
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers'
], function () {
    $routePrefix = config('omen.urlPrefix');

    Route::match(['get'],  sprintf('%s/', $routePrefix), 'OmenController@index')->name('omenInterface');

    // JS Download
    Route::match(['get'], sprintf('%s/download/{file}', $routePrefix), 'OmenController@download')->where('file', '(.*)')->name('omenDownload');

    // Assets
    Route::get(sprintf('%s/{fileUri}', config('omen.assetPath')), 'OmenController@asset')->where('fileUri', '.*')->name('omenAsset');
});
