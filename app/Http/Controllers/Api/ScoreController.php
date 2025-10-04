<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Score;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
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
            'player_id' => 'required|integer|exists:players,id',
            'leaderboard_id' => 'required|integer|exists:leaderboards,id',
            'score' => 'required|integer'
        ]);

        $score = Score::create($validated);

        return response()->json($score,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Score $score)
    {
        return $score;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|integer|exists:players,id',
            'leaderboard_id' => 'required|integer|exists:leaderboards,id',
            'score' => 'required|integer'
        ]);

        $score = Score::where('player_id', $validated['player_id'])
            ->where('leaderboard_id', $validated['leaderboard_id'])
            ->firstOrFail();

        $score->update(['score' => $validated['score']]);

        return response()->json($score);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|integer|exists:players,id',
            'leaderboard_id' => 'required|integer|exists:leaderboards,id'
        ]);

        $deleted = \DB::table('scores')
            ->where('player_id', $validated['player_id'])
            ->where('leaderboard_id', $validated['leaderboard_id'])
            ->delete();

        if ($deleted === 0) {
            return response()->json(['error' => 'Score not found'], 404);
        }

        return response()->json(null, 204);
    }
}
