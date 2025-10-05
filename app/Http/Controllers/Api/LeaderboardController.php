<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Models\Player;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class LeaderboardController extends Controller implements HasMiddleware
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
        return Leaderboard::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_unique' => 'sometimes|boolean',
            'game_id' => [
                'required',
                Rule::exists('games', 'id')->where('user_id', $request->user()->id),
            ],
        ], [
            'game_id.exists' => 'Ce jeu ne vous appartient pas ou n’existe pas.',
        ]);

        $leaderboard = Leaderboard::create($validated);

        return response()->json($leaderboard, 201);
    }

    public function show(Request $request, Leaderboard $leaderboard)
    {
        // Vérifier que le jeu appartient à l'utilisateur
        if ($request->user()->role != "admin") {
            if ($leaderboard->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        return $leaderboard->load('scores')->makeHidden('game');
    }

    public function showWithPagination(Request $request, Leaderboard $leaderboard)
    {
        if ($leaderboard->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $perPage = $request->input('per_page', 15);

        return response()->json([
            'id' => $leaderboard->id,
            'name' => $leaderboard->name,
            'game_id' => $leaderboard->game_id,
            'created_at' => $leaderboard->created_at,
            'updated_at' => $leaderboard->updated_at,
            'scores' => $leaderboard->scores()->orderBy('score', 'desc')->paginate($perPage)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Leaderboard $leaderboard)
    {
        // Vérifier que le jeu appartient à l'utilisateur
        if ($leaderboard->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255'
        ]);

        $leaderboard->update($validated);

        return response()->json($leaderboard);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Leaderboard $leaderboard)
    {
        if ($request->user()->role != "admin") {
            if ($leaderboard->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        $leaderboard->delete();
        return response()->json(null, 204);
    }

    public function getPlayerScore(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'leaderboard_id' => 'required|exists:leaderboards,id'
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

        $score = Score::where('player_id', $validated['player_id'])
            ->where('leaderboard_id', $validated['leaderboard_id'])
            ->firstOrFail();

        return response()->json($score);
    }
}
