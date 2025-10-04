<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Models\Score;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
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
            'game_id' => 'required|integer|exists:games,id'
        ]);
        
        $leaderboard = Leaderboard::create($validated);

        return response()->json($leaderboard, 201);
    }

    public function show(Leaderboard $leaderboard)
    {
        return $leaderboard->load('scores');
    }

    public function showWithPagination(Request $request, Leaderboard $leaderboard)
    {
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
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'game_id' => 'sometimes|exists:games, id'
        ]);

        $leaderboard->update($validated);

        return response()->json($leaderboard);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leaderboard $leaderboard)
    {
        $leaderboard->delete();
        return response()->json(null, 204);
    }

    public function getPlayerScore(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'leaderboard_id' => 'required|exists:leaderboards,id'
        ]);

        $score = Score::where('player_id', $validated['player_id'])
            ->where('leaderboard_id', $validated['leaderboard_id'])
            ->firstOrFail();

        return response()->json($score);
    }
}
