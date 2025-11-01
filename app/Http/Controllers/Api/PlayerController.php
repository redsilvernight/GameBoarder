<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PlayerController extends Controller implements HasMiddleware
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
        return Player::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('players')->where(function ($query) use ($request) {
                    return $query->where('game_id', $request->game_id);
                })
            ],
            'password' => 'required|string|min:6',
            'game_id' => [
                'required',
                'exists:games,id',
                Rule::exists('games', 'id')->where('user_id', $request->user()->id)
            ],
        ], [
            'game_id.exists' => "Ce jeu ne vous appartient pas ou n'existe pas.",
            'name.unique' => 'Ce nom de joueur existe déjà pour ce jeu.',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $player = Player::create($validated);

        return response()->json($player, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Player $player)
    {
        if ($request->user()->role != "admin") {
            if ($player->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        return $player;
    }

    public function getByName(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'game_id' => [
                'required',
                'exists:games,id',
                Rule::exists('games', 'id')->where('user_id', $request->user()->id)
            ],
        ], [
            'game_id.exists' => "Ce jeu ne vous appartient pas ou n'existe pas.",
        ]);

        $player = Player::where('name', $validated['name'])
            ->where('game_id', $validated['game_id'])
            ->first();

        if (!$player) {
            return response()->json(['error' => 'Player not found'], 404);
        }

        return response()->json($player);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player)
    {
        if ($player->game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:6',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $player->update($validated);

        return response()->json($player);
    }

    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'player_name' => 'required|string',
            'player_password' => 'required|string',
            'game_id' => [
                'required',
                'exists:games,id',
                Rule::exists('games', 'id')->where('user_id', $request->user()->id)
            ],
        ], [
            'game_id.exists' => "Ce jeu ne vous appartient pas ou n'existe pas.",
        ]);

        $player = Player::where('name', $validated['player_name'])
            ->where('game_id', $validated['game_id'])
            ->first();

        if (!$player || !Hash::check($validated['player_password'], $player->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json($player);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Player $player)
    {
        if ($request->user()->role != "admin") {
            if ($player->game->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        $player->delete();

        return response()->json(['message' => 'Player deleted successfully'], 200);
    }
}
