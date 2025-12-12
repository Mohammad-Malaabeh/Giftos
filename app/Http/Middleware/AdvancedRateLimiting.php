<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class AdvancedRateLimiting
{
    private const LIMITS = [
        'auth' => [
            'login' => ['attempts' => 5, 'minutes' => 15],
            'register' => ['attempts' => 3, 'minutes' => 60],
            'forgot_password' => ['attempts' => 3, 'minutes' => 60],
            'reset_password' => ['attempts' => 5, 'minutes' => 15],
        ],
        'search' => [
            'products' => ['attempts' => 100, 'minutes' => 60],
        ],
        'general' => [
            'authenticated' => ['attempts' => 1000, 'minutes' => 60],
            'unauthenticated' => ['attempts' => 100, 'minutes' => 60],
        ],
        'sensitive' => [
            'orders' => ['attempts' => 50, 'minutes' => 60],
            'cart' => ['attempts' => 200, 'minutes' => 60],
            'checkout' => ['attempts' => 10, 'minutes' => 15],
        ],
    ];

    public function handle(Request $request, Closure $next)
    {
        $key = $this->getRateLimitKey($request);
        $limit = $this->getRateLimit($request);

        if (!$limit) {
            return $next($request);
        }

        $identifier = $this->getIdentifier($request);
        $redisKey = "rate_limit:{$key}:{$identifier}";

        try {
            $current = Redis::incr($redisKey);

            if ($current === 1) {
                Redis::expire($redisKey, $limit['minutes'] * 60);
            }

            $remaining = max(0, $limit['attempts'] - $current);
            $resetTime = Redis::ttl($redisKey);

            $response = $next($request);

            $response->headers->set('X-RateLimit-Limit', (string) $limit['attempts']);
            $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
            $response->headers->set('X-RateLimit-Reset', (string) (now()->timestamp + $resetTime));

            if ($current > $limit['attempts']) {
                return $this->rateLimitExceeded($limit['minutes']);
            }

            return $response;
        } catch (\Throwable $e) {
            // If Redis is unavailable (e.g., local dev/test without redis), skip rate limiting.
            return $next($request);
        }
    }

    private function getRateLimitKey(Request $request): string
    {
        $routeName = $request->route()->getName();
        $action = $request->route()->getActionMethod();
        
        // Auth endpoints
        if (str_contains($routeName, 'auth')) {
            return "auth.{$action}";
        }
        
        // Search endpoints
        if (str_contains($routeName, 'search')) {
            return "search.products";
        }
        
        // Sensitive endpoints
        if (str_contains($routeName, 'order') || str_contains($routeName, 'cart') || str_contains($routeName, 'checkout')) {
            return "sensitive." . str_replace(['.', '-'], '', $routeName);
        }
        
        // General endpoints
        return $request->user() ? 'general.authenticated' : 'general.unauthenticated';
    }

    private function getRateLimit(Request $request): ?array
    {
        $key = $this->getRateLimitKey($request);
        
        foreach (self::LIMITS as $category => $limits) {
            foreach ($limits as $name => $limit) {
                if ($key === "{$category}.{$name}") {
                    return $limit;
                }
            }
        }
        
        return null;
    }

    private function getIdentifier(Request $request): string
    {
        if ($user = $request->user()) {
            return "user:{$user->id}";
        }
        
        return "ip:" . md5($request->ip());
    }

    private function rateLimitExceeded(int $minutes): JsonResponse
    {
        return response()->json([
            'message' => 'Too many requests. Please try again later.',
            'error' => 'rate_limit_exceeded',
            'retry_after' => $minutes * 60
        ], 429);
    }
}
