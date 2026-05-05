<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($request->user()->role !== 'company') {
            return response()->json([
                'message' => 'Only company owner can access this route.',
            ], 403);
        }

        if ($request->user()->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive.',
            ], 403);
        }

        if (! $request->user()->company_id) {
            return response()->json([
                'message' => 'Company profile is not linked with this account.',
            ], 403);
        }

        return $next($request);
    }
}
