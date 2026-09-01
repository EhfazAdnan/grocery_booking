<?php

use App\Exceptions\InsufficientStockException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions → 401
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid token.',
            ], 401);
        });

        // Handle authorization exceptions → 403
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ], 403);
        });

        // Handle validation exceptions → 422
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        });

        // Handle model not found exceptions → 404
        // Note: Route model binding converts ModelNotFoundException to NotFoundHttpException,
        // so we check the previous exception to determine the correct message
        $exceptions->render(function (NotFoundHttpException $e) {
            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                $model = class_basename($previous->getModel());

                return response()->json([
                    'success' => false,
                    'message' => "{$model} not found.",
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        });

        // Handle model not found exceptions thrown outside of route model binding → 404
        $exceptions->render(function (ModelNotFoundException $e) {
            $model = class_basename($e->getModel());

            return response()->json([
                'success' => false,
                'message' => "{$model} not found.",
            ], 404);
        });

        // Handle insufficient stock exceptions → 422
        $exceptions->render(function (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });

        // Log all unhandled exceptions with context (no sensitive data leaked)
        $exceptions->render(function (\Throwable $e) {
            Log::error('Unhandled exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // In production, don't leak exception details
            $isProduction = app()->isProduction();

            return response()->json([
                'success' => false,
                'message' => $isProduction ? 'An internal server error occurred.' : $e->getMessage(),
            ], 500);
        });
    })->create();
