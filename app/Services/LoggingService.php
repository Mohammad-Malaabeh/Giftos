<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as HttpRequest;

class LoggingService
{
    protected array $context = [];

    public function __construct()
    {
        $this->context = [
            'request_id' => uniqid(),
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Log user activity
     */
    public function logUserActivity(string $action, array $data = [], ?string $level = 'info'): void
    {
        $user = auth()->user();
        
        $context = array_merge($this->context, [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'action' => $action,
            'data' => $data,
        ]);

        Log::channel('activity')->{$level}("User Activity: {$action}", $context);
    }

    /**
     * Log API request
     */
    public function logApiRequest(HttpRequest $request, ?array $response = null, float $duration = 0): void
    {
        $context = array_merge($this->context, [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'endpoint' => $request->route()?->getName(),
            'user_id' => auth()->user()?->id,
            'request_size' => strlen($request->getContent()),
            'duration_ms' => round($duration * 1000, 2),
        ]);

        if ($response) {
            $context['response_status'] = $response['status'] ?? null;
            $context['response_size'] = $response['size'] ?? null;
            $context['response_data'] = $response['data'] ?? null;
        }

        Log::channel('api')->info('API Request', $context);
    }

    /**
     * Log database query
     */
    public function logQuery(string $query, array $bindings, float $time): void
    {
        if ($time > 0.1) { // Log slow queries
            $context = array_merge($this->context, [
                'query' => $query,
                'bindings' => $bindings,
                'execution_time' => $time,
                'user_id' => auth()->user()?->id,
            ]);

            Log::channel('database')->warning('Slow Database Query', $context);
        }
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        $context = array_merge($this->context, [
            'event' => $event,
            'user_id' => auth()->user()?->id,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'data' => $data,
        ]);

        Log::channel('security')->warning("Security Event: {$event}", $context);
    }

    /**
     * Log error
     */
    public function logError(\Throwable $exception, array $context = []): void
    {
        $context = array_merge($this->context, [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => auth()->user()?->id,
        ], $context);

        Log::channel('errors')->error('Application Error', $context);
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, array $metrics): void
    {
        $context = array_merge($this->context, [
            'operation' => $operation,
            'metrics' => $metrics,
            'user_id' => auth()->user()?->id,
        ]);

        Log::channel('performance')->info("Performance: {$operation}", $context);
    }

    /**
     * Log cache operations
     */
    public function logCacheOperation(string $operation, string $key, array $data = []): void
    {
        $context = array_merge($this->context, [
            'operation' => $operation,
            'cache_key' => $key,
            'data' => $data,
        ]);

        Log::channel('cache')->debug("Cache: {$operation}", $context);
    }

    /**
     * Log business event
     */
    public function logBusinessEvent(string $event, array $data = []): void
    {
        $context = array_merge($this->context, [
            'event' => $event,
            'user_id' => auth()->user()?->id,
            'data' => $data,
        ]);

        Log::channel('business')->info("Business Event: {$event}", $context);
    }

    /**
     * Log system event
     */
    public function logSystemEvent(string $event, array $data = []): void
    {
        $context = array_merge($this->context, [
            'event' => $event,
            'data' => $data,
        ]);

        Log::channel('system')->info("System Event: {$event}", $context);
    }

    /**
     * Add custom context
     */
    public function addContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    /**
     * Get current context
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
