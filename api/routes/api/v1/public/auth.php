<?php

/**
 * --------------------------------------------------------------------------
 * PUBLIC - AUTH
 * --------------------------------------------------------------------------
 *
 * Autenticação padrão (cliente / parceiro)
 *
 * URL:
 * /api/v1/public/auth/login
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Common\Auth\AuthController;

// Route::prefix('auth')->group(function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

// });