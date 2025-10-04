<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('games', GameController::class);

    Route::apiResource('players', PlayerController::class);

    Route::get('/leaderboards/{leaderboard}/paginate', [LeaderboardController::class, 'showWithPagination']);
    Route::get('/leaderboards/player-score', [LeaderboardController::class, 'getPlayerScore']);
    Route::apiResource('leaderboards', LeaderboardController::class);

    Route::apiResource('users', UserController::class);

    Route::get('scores', [ScoreController::class, 'index']);
    Route::post('scores', [ScoreController::class, 'store']);
    Route::put('scores', [ScoreController::class, 'update']);
    Route::delete('scores', [ScoreController::class, 'destroy']);
});
