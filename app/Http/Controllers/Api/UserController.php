<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
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
        return User::all();
    }

    public function show(Request $request, User $user)
    {
        if ($user->id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return User::with('games')->findOrFail($user->id);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->role != "admin") {
            if ($user->id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
