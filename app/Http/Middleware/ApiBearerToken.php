<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiBearerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.local_api.bearer_token');
        $requestToken = $request->bearerToken();

        if (!$configuredToken || !$requestToken || !hash_equals($configuredToken, $requestToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
