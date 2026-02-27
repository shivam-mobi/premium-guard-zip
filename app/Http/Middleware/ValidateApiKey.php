<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->header('Authorization');

        // Support "Bearer {key}" format
        if (str_starts_with($apiKey ?? '', 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        $validKey = config('services.api_key');

        if (empty($validKey) || $apiKey !== $validKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
