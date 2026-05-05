<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CandidateUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($request->user()->role !== 'candidate') {
            return response()->json([
                'message' => 'Only candidates can access this route.',
            ], 403);
        }

        if ($request->user()->status !== 'active') {
            return response()->json([
                'message' => 'Candidate account is inactive.',
            ], 403);
        }

        return $next($request);
    }
}
