<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PackagistController;

Route::get('/', function () {
    return view('home');
});

Route::prefix('api')->group(function () {
    Route::get('/packagist/search', [PackagistController::class, 'search']);
    Route::get('/packagist/package/{vendor}/{package}', [PackagistController::class, 'getPackageDetails']);
    Route::get('/packagist/autocomplete', [PackagistController::class, 'autocomplete']);
});
