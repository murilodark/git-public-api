<?php

use Illuminate\Support\Facades\Route;

// Retorna apenas um JSON informando o status da sua API
Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'status' => 'online',
        'environment' => config('app.env')
    ]);
});
