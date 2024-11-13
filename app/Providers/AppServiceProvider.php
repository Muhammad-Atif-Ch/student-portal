<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response as HttpResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        Response::macro('success', function ($code, $message, $data = null) {
            return response()->json([
                'code' => $code,
                'message' => $message,
                'data' => $data
            ]);
        });

        Response::macro('error', function ($code, $message, $error, $status = HttpResponse::HTTP_BAD_REQUEST) {
            if (request()->expectsJson()) {
                return response()->json([
                    'code' => $code,
                    'message' => $message,
                    'error' => [$error]
                ], $status);
            }
        });


        Response::macro('apiResponse', function ($responseType, $code, $message, $data, $status = HttpResponse::HTTP_BAD_REQUEST) {
            return match ($responseType) {
                'SUCCESS' => response()->json([
                    'code' => $code,
                    'message' => $message,
                    'data' => $data
                ]),
                'ERROR' => response()->json([
                    'code' => $code,
                    'message' => $message,
                    'error' => [$data]
                ], $status),
                default => response()->json([
                    'code' => 0,
                    'message' => 'invalid response type',
                ], $status),
            };
        });

        Response::macro('sendResponse', function ($responseType, $code, $message, $data = [], $redirect = null, $route_params = [], $status = HttpResponse::HTTP_BAD_REQUEST) {

            if (request()->expectsJson()) {
                return match ($responseType) {
                    'SUCCESS' => response()->json([
                        'code' => $code,
                        'message' => $message,
                        'data' => $data
                    ]),
                    'ERROR' => response()->json([
                        'code' => $code,
                        'message' => $message,
                        'error' => [$data]
                    ], $status),
                    default => response()->json([
                        'code' => 0,
                        'message' => 'invalid response type',
                    ], $status),
                };
            } else {
                return match ($responseType) {
                    'SUCCESS' => redirect()->route($redirect, $route_params)
                        ->with('success', $message),
                    'ERROR' => back()
                        ->with('error', $message),
                    default => redirect()->back()
                        ->with('warning', 'Invalid response type'),
                };
            }
        });
    }
}
