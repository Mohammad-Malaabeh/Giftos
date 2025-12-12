<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SecurityAuditCommand extends Command
{
    protected $signature = 'security:audit';
    protected $description = 'Run security audit checks';

    public function handle()
    {
        $this->info('Starting security audit...');
        
        $this->checkUnauthenticatedRoutes();
        $this->checkMissingCSRFTokens();
        $this->checkDatabaseExposure();
        $this->checkEnvironmentSecurity();
        
        $this->info('Security audit completed!');
    }

    protected function checkUnauthenticatedRoutes()
    {
        $this->info("\nChecking for potentially unauthenticated routes...");
        
        $unprotectedRoutes = collect(Route::getRoutes())->filter(function ($route) {
            $middleware = $route->middleware();
            return !in_array('auth', $middleware) && 
                   !in_array('auth:api', $middleware) &&
                   !in_array('auth:sanctum', $middleware) &&
                   !in_array('password.confirm', $middleware) &&
                   !in_array('verified', $middleware) &&
                   !in_array('signed', $middleware) &&
                   !in_array('cache.headers', $middleware) &&
                   !in_array('throttle', $middleware) &&
                   !in_array('can:', $middleware) &&
                   !in_array('role:', $middleware) &&
                   !in_array('permission:', $middleware) &&
                   !$this->isPublicRoute($route->uri());
        });

        if ($unprotectedRoutes->isNotEmpty()) {
            $this->warn('Warning: The following routes do not have authentication middleware:');
            $this->table(
                ['Method', 'URI', 'Name', 'Action'],
                $unprotectedRoutes->map(function ($route) {
                    return [
                        'method' => implode('|', $route->methods()),
                        'uri' => $route->uri(),
                        'name' => $route->getName() ?? 'N/A',
                        'action' => $route->getActionName(),
                    ];
                })->toArray()
            );
        } else {
            $this->info('✓ All routes have appropriate authentication middleware.');
        }
    }

    protected function checkMissingCSRFTokens()
    {
        $this->info("\nChecking for missing CSRF tokens...");
        
        $routesWithoutCSRF = collect(Route::getRoutes())->filter(function ($route) {
            $methods = $route->methods();
            $middleware = $route->middleware();
            return in_array('POST', $methods) || 
                   in_array('PUT', $methods) || 
                   in_array('PATCH', $methods) || 
                   in_array('DELETE', $methods);
        })->reject(function ($route) {
            return in_array('api', $route->middleware()) || 
                   in_array('auth:api', $route->middleware()) ||
                   in_array('auth:sanctum', $route->middleware()) ||
                   in_array('csrf', $route->middleware()) ||
                   in_array(\App\Http\Middleware\VerifyCsrfToken::class, $route->middleware());
        });

        if ($routesWithoutCSRF->isNotEmpty()) {
            $this->warn('Warning: The following routes might be missing CSRF protection:');
            $this->table(
                ['Method', 'URI', 'Name', 'Action'],
                $routesWithoutCSRF->map(function ($route) {
                    return [
                        'method' => implode('|', $route->methods()),
                        'uri' => $route->uri(),
                        'name' => $route->getName() ?? 'N/A',
                        'action' => $route->getActionName(),
                    ];
                })->toArray()
            );
        } else {
            $this->info('✓ All form routes have CSRF protection.');
        }
    }

    protected function checkDatabaseExposure()
    {
        $this->info("\nChecking for potential database exposure...");
        
        $sensitiveTables = ['users', 'password_resets', 'personal_access_tokens', 'sessions'];
        $exposedData = [];
        
        foreach ($sensitiveTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $count = DB::table($table)->count();
                $exposedData[] = [
                    'table' => $table,
                    'rows' => $count,
                    'risk' => $count > 0 ? 'HIGH' : 'LOW'
                ];
            }
        }
        
        if (!empty($exposedData)) {
            $this->warn('Warning: The following tables contain sensitive data:');
            $this->table(
                ['Table', 'Rows', 'Risk Level'],
                $exposedData
            );
        } else {
            $this->info('✓ No sensitive data tables found or they are empty.');
        }
    }

    protected function checkEnvironmentSecurity()
    {
        $this->info("\nChecking environment security settings...");
        
        $checks = [
            'APP_DEBUG' => config('app.debug') ? false : true,
            'APP_ENV' => config('app.env') !== 'production' ? false : true,
            'DB_CONNECTION' => config('database.default') !== 'sqlite' ? true : false,
        ];
        
        $issues = [];
        
        foreach ($checks as $key => $isSecure) {
            if (!$isSecure) {
                $issues[] = [
                    'setting' => $key,
                    'value' => config(strtolower(str_replace('_', '.', $key))),
                    'risk' => 'HIGH'
                ];
            }
        }
        
        if (!empty($issues)) {
            $this->warn('Warning: The following environment settings may be insecure:');
            $this->table(
                ['Setting', 'Value', 'Risk Level'],
                $issues
            );
        } else {
            $this->info('✓ Environment settings appear secure.');
        }
    }

    protected function isPublicRoute($uri)
    {
        $publicRoutes = [
            'login', 'register', 'password/reset', 
            'email/verify', 'verification.notice', 
            'verification.verify', 'verification.resend',
            'password/confirm', 'password/email', 'password/reset/*',
            'home', 'products', 'products/*', 'cart', 'cart/*',
            'checkout', 'checkout/*', 'reviews', 'reviews/*',
            'cookies/accept'
        ];

        return in_array($uri, $publicRoutes) || 
               str_starts_with($uri, 'api/') ||
               str_starts_with($uri, '_debugbar/') ||
               str_starts_with($uri, 'horizon/') ||
               str_starts_with($uri, 'telescope/') ||
               str_starts_with($uri, 'stripe/');
    }
}
