<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backwards-compatible shim for legacy usage.
 *
 * This class preserves the original `App\Http\Middleware\CheckRole` type so
 * existing route references keep working while the application uses
 * `RoleMiddleware` as the canonical implementation.
 *
 * @deprecated Use `\App\Http\Middleware\RoleMiddleware` and the `role` middleware alias instead.
 */
class CheckRole extends \App\Http\Middleware\RoleMiddleware
{
    // Intentionally empty - functionality delegated to RoleMiddleware.
}
