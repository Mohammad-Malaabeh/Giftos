<?php

namespace App\Http\Middleware;

use App\Services\CacheService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CacheApiResponse
{
    protected ?CacheService $cacheService = null;
    protected array $cacheableRoutes = [
        'api.v1.products.index',
        'api.v1.products.show',
        'api.v1.products.featured',
        'api.v1.products.latest',
        'api.v1.products.on-sale',
        'api.v1.categories.index',
        'api.v1.categories.show',
        'api.v1.categories.products',
    ];

    protected array $cacheTimes = [
        'api.v1.products.index' => 900, // 15 minutes
        'api.v1.products.show' => 3600, // 1 hour
        'api.v1.products.featured' => 1800, // 30 minutes
        'api.v1.products.latest' => 900, // 15 minutes
        'api.v1.products.on-sale' => 1800, // 30 minutes
        'api.v1.categories.index' => 7200, // 2 hours
        'api.v1.categories.show' => 3600, // 1 hour
        'api.v1.categories.products' => 900, // 15 minutes
    ];

    public function __construct(?CacheService $cacheService = null)
    {
        // Don't use caching in testing environment
        if (!app()->environment('testing')) {
            $this->cacheService = $cacheService;
        }
    }

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Skip caching for authenticated users, non-GET requests, or in testing environment
        if ($request->method() !== 'GET' || $request->user() || !$this->cacheService) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (!$routeName || !in_array($routeName, $this->cacheableRoutes)) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($request, $routeName);

        // Try to get from cache
        $cachedResponse = $this->cacheService->get($cacheKey);

        if ($cachedResponse) {
            return response($cachedResponse['content'])
                ->header('Content-Type', $cachedResponse['headers']['Content-Type'] ?? 'application/json')
                ->header('X-Cached', 'true')
                ->header('X-Cache-Key', $cacheKey);
        }

        // Get response and cache it
        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            $cacheTime = $this->cacheTimes[$routeName] ?? 900;

            $this->cacheService->put($cacheKey, [
                'content' => $response->getContent(),
                'headers' => [
                    'Content-Type' => $response->headers->get('Content-Type'),
                ],
            ], $cacheTime);
        }

        return $response
            ->header('X-Cached', 'false');
    }

    /**
     * Generate cache key for the request
     */
    protected function generateCacheKey(Request $request, string $routeName): string
    {
        $keyParts = [
            'api_response',
            str_replace('.', '_', $routeName),
            md5($request->fullUrl()),
        ];

        return implode('_', $keyParts);
    }

    /**
     * Clear API cache
     */
    public static function clearCache(?string $routeName = null): void
    {
        // Don't clear cache in testing environment
        if (app()->environment('testing')) {
            return;
        }

        $cacheService = app(CacheService::class);

        if ($routeName) {
            // Clear specific route cache
            $pattern = "api_response_" . str_replace('.', '_', $routeName) . "_*";

            // This would need Redis SCAN implementation for pattern matching
            // For now, we'll clear all API cache
            $cacheService->forgetByTags(['api_responses']);
        } else {
            // Clear all API cache
            $cacheService->forgetByTags(['api_responses']);
        }
    }
}
