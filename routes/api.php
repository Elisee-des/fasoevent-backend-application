<?php

use App\Http\Controllers\Api\Public\AuthController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');;

Route::get('/test/hello', [TestController::class, 'hello']);
Route::get('/test/echo', [TestController::class, 'echo']);
Route::get('/test/echo/{test}', [TestController::class, 'echoUrl']);
