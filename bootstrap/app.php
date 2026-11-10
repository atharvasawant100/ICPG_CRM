<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'attendance/tab-close-logout',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            // Blocks deactivated users on every authenticated route.
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /*
         | AJAX / JSON clients must receive a JSON 403 rather than the HTML
         | error page. Registered before the render callback so it wins.
         */
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403);
            }

            return null;
        });
    })->create();
