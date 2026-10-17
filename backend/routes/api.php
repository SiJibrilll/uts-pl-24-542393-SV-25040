<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Seluruh endpoint selain /login wajib menggunakan token Sanctum.
*/

// Endpoint Otentikasi Publik
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Terproteksi Token (Bearer Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Resource CRUD Buku Lengkap
    Route::apiResource('books', BookController::class);
});
