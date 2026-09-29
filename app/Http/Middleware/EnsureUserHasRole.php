<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()?->hasRole(...$roles)) {
            return ApiResponse::error('Anda tidak memiliki role yang diperlukan.', status: 403);
        }

        return $next($request);
    }
}