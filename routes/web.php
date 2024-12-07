<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/manual-test', function () {
    return response()->json(['message' => 'Route web testée avec succès']);
});
