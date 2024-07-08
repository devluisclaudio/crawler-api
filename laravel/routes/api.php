<?php

use App\Http\Controllers\AmazonasBuscaPreco;
use App\Http\Controllers\ParanaMenorPreco;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['aplication' => env('APP_NAME')]);
});

Route::prefix('v1')->group(function () {
    Route::prefix('am')->group(function () {
        Route::resource('buscapreco', AmazonasBuscaPreco::class);
    });
    Route::prefix('pr')->group(function () {
        Route::resource('menorpreco', ParanaMenorPreco::class);
        Route::get('menorpreco/categoria/combustiveis', [ParanaMenorPreco::class, 'combustivel']);
    });
});
