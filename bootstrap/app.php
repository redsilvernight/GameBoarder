<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Quand on doit rendre JSON ? (API routes ou client qui attend JSON)
        $exceptions->shouldRenderJsonWhen(fn(Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson());

        // Unauthenticated (pas loggé) -> 401 JSON
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        });

        // Not authorized (loggé mais pas autorisé) -> 403 JSON
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return response()->json(['error' => 'Unauthorized'], 403);
        });

        // Validation -> 422 JSON avec erreurs
        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json(['errors' => $e->errors()], 422);
        });

        // Ici tu peux ajouter d'autres render/report si besoin...
    })->create();
