<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['aplication' => env('APP_NAME')]);
});