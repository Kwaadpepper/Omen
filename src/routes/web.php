<?php

use Illuminate\Support\Facades\Route;
use Kwaadpepper\Omen\Http\Middleware\CheckCookieCsrfTokenMiddleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as VerifyAndSetCsrfToken;

$middlewareMinimal = include(__DIR__ . '/middlewareMinimal.php');

Route::group([
    'middleware' => array_merge(['omenerrorhandler', 'omenthrottle:60,1'], $middlewareMinimal, [VerifyAndSetCsrfToken::class]),
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers'
], function () {
    $routePrefix = config('omen.urlPrefix');

    Route::match(['get'],  sprintf('%s/', $routePrefix), 'OmenController@index')->name('omenInterface');
});

/**
 * Check CSRF cookie only for assets
 * the csrf token is validated in download controller
 * only if the inode is private
 */
Route::group([
    'as' => 'httpFileSend.',
    'middleware' => array_merge(['omenerrorhandler', 'omenthrottle:60,1'], $middlewareMinimal),
    'namespace' => 'Kwaadpepper\Omen\Http\Controllers'
], function () {
    $routePrefix = config('omen.urlPrefix');

    // JS Download
    Route::match(['get'], sprintf('%s/download/{file}', $routePrefix), 'DownloadController@download')->where('file', '(.*)')->name('omenDownload');

    // Assets
    Route::get(sprintf('%s/{fileUri}', config('omen.assetPath')), 'AssetController@asset')->where('fileUri', '.*')->name('omenAsset')->middleware(CheckCookieCsrfTokenMiddleware::class);
});
