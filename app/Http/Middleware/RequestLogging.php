<?php

namespace App\Http\Middleware;

use App\Services\LoggingService;
use App\Services\MonitoringService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RequestLogging
{
    protected LoggingService $loggingService;
    protected MonitoringService $monitoringService;
    protected float $startTime;

    public function __construct(LoggingService $loggingService, MonitoringService $monitoringService)
    {
        $this->loggingService = $loggingService;
        $this->monitoringService = $monitoringService;
        $this->startTime = microtime(true);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Calculate request duration
        $duration = microtime(true) - $this->startTime;

        // Log API requests
        if ($request->is('api/*')) {
            $this->logApiRequest($request, $response, $duration);
        }

        // Log slow requests
        if ($duration > 2.0) { // 2 seconds threshold
            $this->logSlowRequest($request, $duration);
        }

        // Record performance metrics
        $this->recordMetrics($request, $response, $duration);

        return $response;
    }

    /**
     * Log API request details
     */
    protected function logApiRequest(Request $request, Response $response, float $duration): void
    {
        $responseData = [
            'status' => $response->getStatusCode(),
            'size' => strlen($response->getContent()),
        ];

        // Only log response data for successful requests and small payloads
        if ($response->isSuccessful() && $response->getStatusCode() !== 204) {
            $content = $response->getContent();
            if (strlen($content) < 1000) {
                try {
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $responseData['data'] = $decoded;
                    }
                } catch (\Exception $e) {
                    // Ignore JSON decode errors
                }
            }
        }

        $this->loggingService->logApiRequest($request, $responseData, $duration);
    }

    /**
     * Log slow requests
     */
    protected function logSlowRequest(Request $request, float $duration): void
    {
        $this->loggingService->logPerformance('slow_request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'duration_ms' => round($duration * 1000, 2),
            'user_id' => auth()->user()?->id,
        ]);
    }

    /**
     * Record performance metrics
     */
    protected function recordMetrics(Request $request, Response $response, float $duration): void
    {
        $tags = [
            'method' => $request->method(),
            'route' => $request->route()?->getName() ?? 'unknown',
            'status' => $response->getStatusCode(),
        ];

        // Record response time
        $this->monitoringService->recordMetric('response_time', $duration * 1000, $tags);

        // Record request count
        $this->monitoringService->recordMetric('request_count', 1, $tags);

        // Record error count
        if ($response->isClientError() || $response->isServerError()) {
            $this->monitoringService->recordMetric('error_count', 1, $tags);
        }

        // Record API specific metrics
        if ($request->is('api/*')) {
            $this->monitoringService->recordMetric('api_request_count', 1, $tags);
            
            if ($response->isSuccessful()) {
                $this->monitoringService->recordMetric('api_success_count', 1, $tags);
            } else {
                $this->monitoringService->recordMetric('api_error_count', 1, $tags);
            }
        }
    }

    /**
     * Handle request termination
     */
    public function terminate(Request $request, Response $response): void
    {
        // Log user activity for authenticated users
        if (auth()->check() && !in_array($request->method(), ['GET', 'HEAD'])) {
            $action = $this->getUserAction($request);
            $this->loggingService->logUserActivity($action, [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $response->getStatusCode(),
            ]);
        }
    }

    /**
     * Get user action from request
     */
    protected function getUserAction(Request $request): string
    {
        $route = $request->route();
        if ($route && $route->getName()) {
            return $route->getName();
        }

        // Fallback to method + path
        return $request->method() . ' ' . $request->path();
    }
}
