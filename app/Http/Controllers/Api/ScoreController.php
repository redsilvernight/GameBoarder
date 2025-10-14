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
            'score' => 'required|numeric',
        ]);

        $player = Player::findOrFail($validated['player_id']);
        $leaderboard = Leaderboard::findOrFail($validated['leaderboard_id']);

        // Vérifie si le leaderboard est unique
        if ($leaderboard->is_unique) {
            $existingScore = Score::where('player_id', $player->id)
                ->where('leaderboard_id', $leaderboard->id)
                ->first();

            if ($existingScore) {
                // Met simplement à jour le score existant
                $existingScore->update(['score' => $validated['score']]);

                return response()->json([
                    'message' => 'Score updated successfully (unique leaderboard)',
                    'score' => $existingScore
                ], 200);
            }
        }

        // Si le leaderboard n’est pas unique, ou si aucun score existant trouvé
        $score = Score::create([
            'player_id' => $player->id,
            'leaderboard_id' => $leaderboard->id,
            'score' => $validated['score'],
        ]);

        return response()->json([
            'message' => 'Score created successfully',
            'score' => $score
        ], 201);
    }

    public function storeHighScore(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'leaderboard_id' => 'required|exists:leaderboards,id',
            'score' => 'required|integer'
        ]);

        $leaderboard = Leaderboard::with('game')->findOrFail($validated['leaderboard_id']);

        if ($leaderboard->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized - Leaderboard does not belong to your game'], 403);
        }

        $player = Player::with('game')->findOrFail($validated['player_id']);
        if ($player->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized - Player does not belong to your game'], 403);
        }

        // Récupérer le meilleur score actuel de ce joueur sur ce leaderboard
        $bestScore = Score::where('player_id', $validated['player_id'])
            ->where('leaderboard_id', $validated['leaderboard_id'])
            ->orderByDesc('score')
            ->first();

        if ($leaderboard->is_unique) {
            // Mode unique → un seul score : update si meilleur, sinon rien
            if ($bestScore) {
                if ($validated['score'] > $bestScore->score) {
                    $bestScore->update(['score' => $validated['score']]);
                    return response()->json([
                        'message' => 'Score updated',
                        'score' => $bestScore
                    ], 200);
                }

                return response()->json([
                    'message' => 'Score not improved',
                    'current_high_score' => $bestScore
                ], 200);
            }

            // Premier score
            $score = Score::create($validated);
            return response()->json($score, 201);
        }

        // Mode non-unique → plusieurs scores possibles, mais seulement si meilleur que le meilleur existant
        if ($bestScore && $validated['score'] <= $bestScore->score) {
            return response()->json([
                'message' => 'Score not high enough to be recorded',
                'current_high_score' => $bestScore
            ], 200);
        }

        // Nouveau meilleur score → enregistrer
        $score = Score::create($validated);

        return response()->json($score, 201);
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

    public function destroy(Request $request, Score $score)
    {
        // Vérification admin → autorisé direct
        if ($request->user()->role !== 'admin') {
            // Charger le joueur et le jeu associés au score
            $score->load('player.game');

            // Vérifier que le jeu appartient à l'utilisateur connecté
            if ($score->player->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized - This score does not belong to your game'], 403);
            }
        }

        $score->delete();

        return response()->json(['message' => 'Score deleted successfully'], 200);
    }
}
