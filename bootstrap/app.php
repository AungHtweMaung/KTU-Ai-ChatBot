<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Services\Chat\Exceptions\ChatException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Domain-level chat failures (missing API key, upstream provider
        // timeout, malformed AI response, etc.) always render as a friendly
        // 502 JSON message the frontend can display verbatim — no matter
        // where in the call chain they were thrown.
        $exceptions->render(function (ChatException $e, Request $request) {
            if ($request->is('chat/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 502);
            }
        });

        $exceptions->respond(function ($response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if (! in_array($status, [403, 404, 500, 503], true) || $request->expectsJson()) {
                return $response;
            }

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
