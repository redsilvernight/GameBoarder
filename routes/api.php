<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\API\SaveController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes admin uniquement
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::apiResource('users', UserController::class);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('games', GameController::class);

    Route::post('/players/by-name', [PlayerController::class, 'getByName']);
    Route::post('/players/authenticate', [PlayerController::class, 'authenticate']);
    Route::apiResource('players', PlayerController::class);

    Route::get('/leaderboards/{leaderboard}/paginate', [LeaderboardController::class, 'showWithPagination']);
    Route::get('/leaderboards/player-score', [LeaderboardController::class, 'getPlayerScore']);
    Route::apiResource('leaderboards', LeaderboardController::class);

    Route::get('scores', [ScoreController::class, 'index']);
    Route::post('scores/highscore', [ScoreController::class, 'storeHighScore']);
    Route::post('scores', [ScoreController::class, 'store']);
    Route::delete('scores/{score}', [ScoreController::class, 'destroy']);

    Route::get('saves/{game}', [SaveController::class, 'allSaves']);
    Route::delete('saves/{save}', [SaveController::class, 'destroy']);

    Route::prefix('saves/{game}/{player}')->group(function () {
        Route::get('/', [SaveController::class, 'index']);
        Route::post('/', [SaveController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/{slot}', [SaveController::class, 'show']);
    });
});
