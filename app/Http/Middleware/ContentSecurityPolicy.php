<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $viteHosts = $this->viteDevHosts();

        $scriptSrc = [
            "'self'",
            "'unsafe-inline'",
            "'unsafe-eval'",
            "https://js.stripe.com",
            "https://cdn.jsdelivr.net",
            "https://cdn.jsdelivr.net/npm",
            "http://localhost:5173",
            "http://127.0.0.1:5173",
        ];

        $styleSrc = [
            "'self'",
            "'unsafe-inline'",
            "https://cdn.jsdelivr.net",
            "https://fonts.bunny.net",
        ];

        $connectSrc = [
            "'self'",
            "data:",
            "blob:",
            "https://api.stripe.com",
            "https://r.stripe.com",
            "https://cdn.jsdelivr.net",
            "https://cdn.jsdelivr.net/npm",
        ];

        if (app()->environment('local') || $viteHosts['enabled']) {
            foreach ($viteHosts['http'] as $h) {
                $scriptSrc[] = $h;
                $styleSrc[] = $h;
                $connectSrc[] = $h;
            }
            foreach ($viteHosts['ws'] as $h) {
                $connectSrc[] = $h;
            }
        }

        $fontSrc = [
            "'self'",
            "https://fonts.bunny.net",
        ];

        $csp = implode('; ', [
            "default-src 'self'",
            'script-src ' . implode(' ', array_unique($scriptSrc)),
            'style-src ' . implode(' ', array_unique($styleSrc)),
            "img-src 'self' data: blob: https://*.stripe.com https://via.placeholder.com;",
            'font-src ' . implode(' ', array_unique($fontSrc)),
            'connect-src ' . implode(' ', array_unique($connectSrc)),
            "frame-src https://js.stripe.com",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self' https://js.stripe.com https://hooks.stripe.com",
            "object-src 'none'",
            "upgrade-insecure-requests",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    protected function viteDevHosts(): array
    {
        $ports = [5173];
        $httpHosts = ['http://localhost', 'http://127.0.0.1'];
        $wsHosts = ['ws://localhost', 'ws://127.0.0.1'];

        $allowHttp = [];
        $allowWs = [];

        foreach ($ports as $p) {
            foreach ($httpHosts as $h) $allowHttp[] = "{$h}:{$p}";
            foreach ($wsHosts as $h) $allowWs[] = "{$h}:{$p}";
        }

        return [
            'enabled' => app()->environment('local'),
            'http' => $allowHttp,
            'ws' => $allowWs,
        ];
    }
}
