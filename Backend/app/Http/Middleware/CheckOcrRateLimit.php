<?php

namespace App\Http\Middleware;

use App\Models\RateLimit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOcrRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();
        $rateLimit = RateLimit::canPerformAction($ipAddress, RateLimit::ACTION_UPLOAD);

        if (!$rateLimit['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateLimit['message'],
                'remaining_attempts' => 0,
                'blocked_until' => $rateLimit['blocked_until']?->toIso8601String(),
            ], 429);
        }

        // Add rate limit info to response headers
        $response = $next($request);
        
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response->headers->set('X-RateLimit-Remaining', $rateLimit['remaining']);
        }

        return $response;
    }
}

