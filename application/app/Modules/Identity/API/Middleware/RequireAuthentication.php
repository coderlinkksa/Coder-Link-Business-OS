<?php

namespace App\Modules\Identity\API\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Guards routes that require an authenticated session.
 * Returns 401 JSON for unauthenticated requests rather than a redirect,
 * keeping the API consistent for n8n and other consumers.
 */
class RequireAuthentication
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (! $request->user()) {
            return response()->json(
                ['message' => 'Unauthenticated.'],
                Response::HTTP_UNAUTHORIZED,
            );
        }

        return $next($request);
    }
}
