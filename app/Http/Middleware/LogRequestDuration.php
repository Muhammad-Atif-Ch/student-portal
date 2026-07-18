<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestDuration
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        if ($request->routeIs('admin.quiz.question.store')) {
            Log::info('[CreateQuestion] Full request lifecycle', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'total_ms' => round((microtime(true) - $start) * 1000, 2),
                'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        }

        return $response;
    }
}
