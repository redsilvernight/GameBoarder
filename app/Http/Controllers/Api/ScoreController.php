<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Models\Player;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ScoreController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin', only: ['index']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Score::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'leaderboard_id' => 'required|exists:leaderboards,id',
            'score' => 'required|integer'
        ]);

        // Récupérer le leaderboard avec son jeu
        $leaderboard = Leaderboard::with('game')->findOrFail($validated['leaderboard_id']);

        // Vérifier que le jeu du leaderboard appartient à l'utilisateur
        if ($leaderboard->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized - Leaderboard does not belong to your game'], 403);
        }

        // Récupérer le joueur avec son jeu
        $player = Player::with('game')->findOrFail($validated['player_id']);

        // Vérifier que le joueur appartient à un jeu de l'utilisateur
        if ($player->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized - Player does not belong to your game'], 403);
        }

        if ($leaderboard->is_unique) {
            // Vérifier si un score existe déjà
            $existingScore = Score::where('player_id', $validated['player_id'])
                ->where('leaderboard_id', $validated['leaderboard_id'])
                ->first();

            if ($existingScore) {
                // Update uniquement si le nouveau score est meilleur
                if ($validated['score'] > $existingScore->score) {
                    $existingScore->update(['score' => $validated['score']]);
                    return response()->json($existingScore, 200);
                }
                return response()->json([
                    'message' => 'Score not improved',
                    'current_score' => $existingScore
                ], 200);
            }
        }

        // Créer un nouveau score (mode non-unique ou premier score en mode unique)
        $score = Score::create($validated);

        return response()->json($score, 201);
    }

    public function storeHighScore(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'leaderboard_id' => 'required|exists:leaderboards,id',
            'score' => 'required|integer'
        ]);

        // Récupérer le leaderboard avec son jeu
        $leaderboard = Leaderboard::with('game')->findOrFail($validated['leaderboard_id']);

        // Vérifier que le jeu du leaderboard appartient à l'utilisateur
        if ($leaderboard->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized - Leaderboard does not belong to your game'], 403);
        }

        // Récupérer le joueur avec son jeu
        $player = Player::with('game')->findOrFail($validated['player_id']);

        // Vérifier que le joueur appartient à un jeu de l'utilisateur
        if ($player->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized - Player does not belong to your game'], 403);
        }

        // Récupérer le meilleur score existant du joueur
        $existingScore = Score::where('player_id', $validated['player_id'])
            ->where('leaderboard_id', $validated['leaderboard_id'])
            ->orderBy('score', 'desc')
            ->first();

        // Si pas de score existant, créer
        if (!$existingScore) {
            $score = Score::create($validated);
            return response()->json($score, 201);
        }

        // Si le nouveau score est meilleur
        if ($validated['score'] > $existingScore->score) {
            if ($leaderboard->is_unique) {
                // Mode unique : update le score existant
                $existingScore->update(['score' => $validated['score']]);
                return response()->json($existingScore, 200);
            } else {
                // Mode non-unique : créer un nouveau score
                $score = Score::create($validated);
                return response()->json($score, 201);
            }
        }

        // Score pas meilleur
        return response()->json([
            'message' => 'Score not improved',
            'current_high_score' => $existingScore
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Score $score)
    {
        if ($request->user()->role != "admin") {
            if ($score->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        return $score;
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|integer|exists:players,id',
            'leaderboard_id' => 'required|integer|exists:leaderboards,id'
        ]);

        if ($request->user()->role != "admin") {
            // Récupérer le leaderboard avec son jeu
            $leaderboard = Leaderboard::with('game')->findOrFail($validated['leaderboard_id']);

            // Vérifier que le jeu du leaderboard appartient à l'utilisateur
            if ($leaderboard->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized - Leaderboard does not belong to your game'], 403);
            }

            // Récupérer le joueur avec son jeu
            $player = Player::with('game')->findOrFail($validated['player_id']);

            // Vérifier que le joueur appartient à un jeu de l'utilisateur
            if ($player->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized - Player does not belong to your game'], 403);
            }
        }

        $deleted = Score::where('player_id', $validated['player_id'])
            ->where('leaderboard_id', $validated['leaderboard_id'])
            ->delete();

        if ($deleted === 0) {
            return response()->json(['error' => 'Score not found'], 404);
        }

        return response()->json(null, 204);
    }
}
