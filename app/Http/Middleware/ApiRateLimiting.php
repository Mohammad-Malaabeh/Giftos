<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\ThrottleRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class ApiRateLimiting extends ThrottleRequests
{
    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return sha1($user->id . '|' . $request->ip() . '|' . $request->route()->getName());
        }

        return sha1($request->ip() . '|' . $request->route()->getName());
    }

    protected function buildResponse(string $key, int $maxAttempts, int $remainingSeconds, int $retryAfter = null): Response
    {
        $response = parent::buildResponse($key, $maxAttempts, $remainingSeconds, $retryAfter);
        
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) $remainingSeconds);
        
        if ($retryAfter !== null) {
            $response->headers->set('X-RateLimit-Reset', (string) (now()->timestamp + $retryAfter));
            $response->headers->set('Retry-After', (string) $retryAfter);
        }

        return $response;
    }
}
