<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class GameController extends Controller implements HasMiddleware
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
        return Game::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('games')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user()->id);
                }),
            ],
            'save_mode' => ['required', 'string', 'in:single,multiple'], // adapte selon tes modes
            'max_save_slots' => ['nullable', 'integer', 'min:1'],
        ]);

        // Si le mode est "single", forcer max_save_slots à 1
        if ($validated['save_mode'] === 'single') {
            $validated['max_save_slots'] = 1;
        }

        // Sinon, valeur par défaut si non définie (optionnel)
        $validated['max_save_slots'] = $validated['max_save_slots'] ?? 3;

        $game = $request->user()->games()->create($validated);

        return response()->json($game, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Game $game)
    {
        if ($request->user()->role != "admin") {
            if ($game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        return $game->load(['players', 'leaderboards']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        if ($game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255'
        ]);

        $validated['user_id'] = $request->user()->id;
        $game->update($validated);
        return response()->json($game);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Game $game)
    {
        if ($request->user()->role != "admin") {
            if ($game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        $game->delete();
        return response()->json(['message' => 'Game deleted successfully'], 200);
    }
}
