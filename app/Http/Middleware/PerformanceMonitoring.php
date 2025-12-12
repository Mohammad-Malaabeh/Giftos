<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PerformanceMonitoring
{
    public function handle($request, Closure $next)
    {
        if (!config('app.debug')) {
            return $next($request);
        }

        $requestId = (string) Str::uuid();
        $start = microtime(true);

        $response = $next($request);

        $end = microtime(true);
        $duration = round(($end - $start) * 1000, 2); // in milliseconds

        $this->logRequest($request, $response, $duration, $requestId);

        return $response;
    }

    protected function logRequest($request, $response, $duration, $requestId)
    {
        $method = $request->method();
        $uri = $request->path();
        $status = $response->status();
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $userId = optional($request->user())->id;

        $logData = [
            'request_id' => $requestId,
            'method' => $method,
            'uri' => $uri,
            'status' => $status,
            'duration_ms' => $duration,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'user_id' => $userId,
            'timestamp' => now()->toDateTimeString(),
        ];

        // Log slow requests
        if ($duration > 1000) { // More than 1 second
            Log::warning('Slow request detected', $logData);
        }

        // Log all requests to a dedicated channel
        Log::channel('performance')->info('Request processed', $logData);

        // Store in database for analysis
        if (config('performance.log_to_database', false)) {
            \App\Models\PerformanceLog::create($logData);
        }
    }
}
