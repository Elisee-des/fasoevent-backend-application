<?php

use App\Http\Controllers\Api\Public\AuthController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Private\Admin\CityController;
use App\Http\Controllers\Api\Private\Admin\EventController;
use App\Http\Controllers\Api\Private\User\EventUserController;
use App\Http\Controllers\Api\Public\EventPublicController;

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

//Public Routes
// Routes publiques pour les événements
Route::get('/public/events', [EventPublicController::class, 'index'])->name('public.events.index');
Route::get('/public/events/{id}', [EventPublicController::class, 'show'])->name('public.events.show');
Route::get('/public/events/city/{cityId}', [EventPublicController::class, 'byCity'])->name('public.events.byCity');
Route::get('/public/events/upcoming', [EventPublicController::class, 'upcoming'])->name('public.events.upcoming');

// Routes protégées pour les réservations d'événements
Route::middleware('auth:sanctum')->group(function () {

    Route::middleware(['role:admin'])->group(function () {
        // Routes pour les villes
        Route::apiResource('cities', CityController::class);

        // Routes pour les événements
        Route::apiResource('events', EventController::class);

        // Route pour toggle le statut
        Route::patch('events/{id}/toggle-status', [EventController::class, 'toggleStatus'])
            ->name('events.toggle-status');
    });



    Route::get('/user/events/reservations', [EventUserController::class, 'index'])->name('user.events.reservations');
    Route::post('/user/events/{eventId}/reserve', [EventUserController::class, 'store'])->name('user.events.reserve');
    Route::delete('/user/events/{eventId}/cancel', [EventUserController::class, 'destroy'])->name('user.events.cancel');
    Route::get('/user/events/{eventId}/check', [EventUserController::class, 'checkRegistration'])->name('user.events.check');
});
