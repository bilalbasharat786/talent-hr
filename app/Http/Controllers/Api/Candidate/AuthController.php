<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)
            ->where('role', 'candidate')
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid candidate credentials.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Candidate account is inactive.',
            ], 403);
        }

        $token = $user->createToken('candidate-user-token')->plainTextToken;

        ActivityLogger::log(
            'login',
            'candidate_auth',
            'Candidate logged in.',
            $request,
            $user->id
        );

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->loadCount(['jobApplications', 'internships']),
        ]);
    }

    public function logout(Request $request)
    {
        ActivityLogger::log(
            'logout',
            'candidate_auth',
            'Candidate logged out.',
            $request
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
