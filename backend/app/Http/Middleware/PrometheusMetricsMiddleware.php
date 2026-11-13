<?php

namespace App\Http\Middleware;

use App\Http\Controllers\MetricsController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrometheusMetricsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Record metrics for all requests except /metrics itself
        if (!$request->is('metrics') && !$request->is('api/metrics')) {
            MetricsController::incrementRequest(
                $request->method(),
                $request->path(),
                $response->getStatusCode()
            );
        }

        return $response;
    }
}
