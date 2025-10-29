<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameSaveRequest;
use App\Models\Game;
use App\Models\GameSave;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SaveController extends Controller
{
    use AuthorizesRequests;

    public function allSaves(int $gameId): JsonResponse
    {
        $game = Game::find($gameId);

        if (!$game) {
            return response()->json(['error' => 'Jeu introuvable'], 404);
        }

        if ($game->user_id !== auth()->id()) {
            return response()->json(['error' => 'Accès non autorisé à ce jeu'], 403);
        }

        $saves = GameSave::where('game_id', $gameId)
            ->with('player:id,name')
            ->orderBy('player_id')
            ->orderBy('slot')
            ->get()
            ->map(function ($save) {
                return [
                    'id' => $save->id,
                    'player_id' => $save->player_id,
                    'player_pseudo' => $save->player->name,
                    'slot' => $save->slot,
                    'file_size' => $save->file_size,
                    'checksum' => $save->checksum,
                    'created_at' => $save->formatted_created_at,
                    'updated_at' => $save->formatted_updated_at
                ];
            });

        return response()->json([
            'message' => 'Sauvegardes du jeu récupérées avec succès',
            'data' => $saves,
        ]);
    }

    public function index(int $gameId, int $playerId): JsonResponse
    {
        $game = Game::find($gameId);

        if (!$game) {
            return response()->json(['error' => 'Jeu introuvable'], 404);
        }

        if ($game->user_id !== auth()->id()) {
            return response()->json(['error' => 'Accès non autorisé à ce jeu'], 403);
        }

        $player = Player::where('id', $playerId)
            ->where('game_id', $gameId)
            ->first();

        if (!$player) {
            return response()->json(['error' => 'Joueur introuvable pour ce jeu'], 404);
        }

        $saves = $player->gameSaves()
            ->orderBy('slot')
            ->get()
            ->map(function ($save) {
                return [
                    'id' => $save->id,
                    'slot' => $save->slot,
                    'file_size' => $save->file_size,
                    'checksum' => $save->checksum,
                    'created_at' => $save->formatted_created_at,
                    'updated_at' => $save->formatted_updated_at,
                ];
            });

        return response()->json([
            'message' => 'Sauvegardes récupérées avec succès',
            'data' => $saves,
        ]);
    }

    public function store(StoreGameSaveRequest $request, int $gameId, int $playerId): JsonResponse
    {
        $game = Game::find($gameId);

        if (!$game) {
            return response()->json(['error' => 'Jeu introuvable'], 404);
        }

        if ($game->user_id !== auth()->id()) {
            return response()->json(['error' => 'Accès non autorisé à ce jeu'], 403);
        }

        $player = Player::where('id', $playerId)
            ->where('game_id', $gameId)
            ->first();

        if (!$player) {
            return response()->json(['error' => 'Joueur introuvable pour ce jeu'], 404);
        }

        $requestedSlot = $request->input('slot', 1);

        if ($game->save_mode === 'single') {
            $slot = 1;
            $existingSave = $player->gameSaves()->where('slot', 1)->first();
            if ($existingSave) {
                $existingSave->delete();
            }
        } else {
            if ($requestedSlot > $game->max_save_slots) {
                return response()->json([
                    'error' => "Le slot demandé dépasse la limite de {$game->max_save_slots} sauvegardes",
                ], 422);
            }

            $slot = $requestedSlot;
            $existingSaveCount = $player->gameSaves()->count();
            $existingSave = $player->gameSaves()->where('slot', $slot)->first();

            if (!$existingSave && $existingSaveCount >= $game->max_save_slots) {
                return response()->json([
                    'error' => "Limite de {$game->max_save_slots} sauvegardes atteinte",
                ], 422);
            }

            if ($existingSave) {
                $existingSave->delete();
            }
        }

        $content = json_encode($request->input('content'), JSON_PRETTY_PRINT);
        $fileSize = strlen($content);

        if ($fileSize > 50 * 1024 * 1024) {
            return response()->json([
                'error' => 'La taille de la sauvegarde dépasse la limite de 50MB',
            ], 422);
        }

        $checksum = hash('sha256', $content);
        $directory = "saves/{$game->id}/{$player->id}";
        $filename = "{$slot}.sav";
        $filePath = "{$directory}/{$filename}";

        Storage::makeDirectory($directory);
        Storage::put($filePath, $content);

        $save = GameSave::create([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'slot' => $slot,
            'file_path' => $filePath,
            'checksum' => $checksum,
            'file_size' => $fileSize,
        ]);

        return response()->json([
            'message' => 'Sauvegarde créée avec succès',
            'data' => [
                'id' => $save->id,
                'slot' => $save->slot,
                'file_size' => $save->file_size,
                'checksum' => $save->checksum,
                'created_at' => $save->created_at,
                'updated_at' => $save->updated_at,
            ],
        ], 201);
    }

    public function show(int $gameId, int $playerId, int $slot): JsonResponse
    {
        $game = Game::find($gameId);

        if (!$game) {
            return response()->json(['error' => 'Jeu introuvable'], 404);
        }

        if ($game->user_id !== auth()->id()) {
            return response()->json(['error' => 'Accès non autorisé à ce jeu'], 403);
        }

        $player = Player::where('id', $playerId)
            ->where('game_id', $gameId)
            ->first();

        if (!$player) {
            return response()->json(['error' => 'Joueur introuvable pour ce jeu'], 404);
        }

        $save = $player->gameSaves()->where('slot', $slot)->first();

        if (!$save) {
            return response()->json(['error' => 'Sauvegarde introuvable'], 404);
        }

        if (!Storage::exists($save->file_path)) {
            return response()->json(['error' => 'Fichier de sauvegarde introuvable'], 404);
        }

        $content = Storage::get($save->file_path);
        $decodedContent = json_decode($content, true);

        return response()->json([
            'message' => 'Sauvegarde récupérée avec succès',
            'data' => [
                'id' => $save->id,
                'slot' => $save->slot,
                'content' => $decodedContent,
                'file_size' => $save->file_size,
                'checksum' => $save->checksum,
                'created_at' => $save->created_at,
                'updated_at' => $save->updated_at,
            ],
        ]);
    }

    public function destroy(int $saveId): JsonResponse
    {
        $save = GameSave::find($saveId);

        if (!$save) {
            return response()->json(['error' => 'Sauvegarde introuvable'], 404);
        }

        if ($save->game->user_id !== auth()->id()) {
            return response()->json(['error' => 'Accès non autorisé à cette sauvegarde'], 403);
        }

        $save->delete();

        return response()->json([
            'message' => 'Sauvegarde supprimée avec succès',
        ]);
    }
}
