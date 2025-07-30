<?php

use App\Console\Commands\ManageMemberships;
use App\Console\Commands\TestGoogleApiConnection;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::with('60'), // Fixed syntax
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\CheckDeviceId::class,
            \App\Http\Middleware\CheckMembership::class,
        ]);

        // OR register as a named middleware (to apply selectively)
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'device.check' => \App\Http\Middleware\CheckDeviceId::class,
            'membership.check' => \App\Http\Middleware\CheckMembership::class,
        ]);
    })
    ->withCommands([
        ManageMemberships::class,
        TestGoogleApiConnection::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            // Handle API exceptions only for JSON requests
            if ($request->expectsJson() || $request->is('api/*')) {
                $statusCode = 500;
                $response = [
                    'success' => false,
                    'message' => 'Server Error',
                ];

                // Add debug info when in local environment
                if (config('app.debug')) {
                    $response['error'] = $e->getMessage();
                    $response['trace'] = $e->getTrace();
                }

                // Handle specific exception types
                switch (true) {
                    case $e instanceof AuthenticationException:
                        $statusCode = 401;
                        $response['message'] = 'Unauthenticated';
                        break;

                    case $e instanceof AuthorizationException:
                        $statusCode = 403;
                        $response['message'] = 'Unauthorized';
                        break;

                    case $e instanceof ValidationException:
                        $statusCode = 422;
                        $response['message'] = 'Validation failed';
                        $response['errors'] = $e->errors();
                        break;

                    case $e instanceof ModelNotFoundException:
                    case $e instanceof NotFoundHttpException:
                        $statusCode = 404;
                        $response['message'] = 'Resource not found';
                        break;

                    case $e instanceof HttpException:
                        $statusCode = $e->getStatusCode();
                        $response['message'] = $e->getMessage() ?: Response::$statusTexts[$statusCode] ?? 'Unknown Error';
                        break;

                    default:
                        // Use exception's HTTP code if available
                        if (method_exists($e, 'getStatusCode')) {
                            $statusCode = $e->getStatusCode();
                        }
                        break;
                }

                // Ensure valid HTTP status code
                $statusCode = $statusCode >= 100 && $statusCode <= 599 ? $statusCode : 500;

                return response()->json($response, $statusCode);
            }

            // Return default response for non-API requests
            return null;
        });
    })->create();
