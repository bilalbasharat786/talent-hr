<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HrUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($request->user()->role !== 'hr') {
            return response()->json([
                'message' => 'Only HR users can access this route.',
            ], 403);
        }

        if ($request->user()->status !== 'active') {
            return response()->json([
                'message' => 'Your HR account is inactive.',
            ], 403);
        }

        if (! $request->user()->company_id) {
            return response()->json([
                'message' => 'HR account is not linked with any company.',
            ], 403);
        }

        if (! $request->user()->company || $request->user()->company->status !== 'approved') {
            return response()->json([
                'message' => 'Your company must be verified before HR operations can start.',
            ], 403);
        }

        return $next($request);
    }
}

