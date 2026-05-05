<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Models\User;
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

        $user = User::with('company')
            ->where('email', $request->email)
            ->where('role', 'hr')
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid HR credentials.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'HR account is inactive.',
            ], 403);
        }

        if (! $user->company_id || ! $user->company || $user->company->status !== 'approved') {
            return response()->json([
                'message' => 'Company must be verified before HR login is allowed.',
            ], 403);
        }

        $token = $user->createToken('hr-user-token')->plainTextToken;

        ActivityLogger::log(
            'login',
            'hr_auth',
            'HR user logged in.',
            $request,
            $user->id
        );

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'user' => $user,
            'company' => $user->company,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('company'),
        ]);
    }

    public function logout(Request $request)
    {
        ActivityLogger::log(
            'logout',
            'hr_auth',
            'HR user logged out.',
            $request
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}


