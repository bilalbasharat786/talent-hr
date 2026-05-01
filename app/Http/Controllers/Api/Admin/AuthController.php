<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogger;


class AuthController extends Controller
{
  public function login(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $admin = User::where('email', $request->email)
        ->where('role', 'super_admin')
        ->first();

    if (! $admin || ! Hash::check($request->password, $admin->password)) {
        throw ValidationException::withMessages([
            'email' => ['Invalid admin credentials.'],
        ]);
    }

    if ($admin->status !== 'active') {
        return response()->json([
            'message' => 'Admin account is inactive.',
        ], 403);
    }

    $token = $admin->createToken('super-admin-token')->plainTextToken;

    ActivityLogger::log('login', 'authentication', 'Super admin logged in.', $request, $admin->id);

    return response()->json([
        'token' => $token,
        'role' => $admin->role,
        'user' => $admin,
    ]);
}


    public function logout(Request $request)
    {
           ActivityLogger::log('logout', 'authentication', 'Super admin logged out.', $request);
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}

