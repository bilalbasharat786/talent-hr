<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'account_settings' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'two_factor_enabled' => (bool) $user->two_factor_enabled,
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('company-owner-token')->plainTextToken;

        ActivityLogger::log(
            'change_password',
            'company_account_settings',
            "Company owner {$user->email} changed password.",
            $request
        );

        Notification::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'type' => 'system_alert',
            'title' => 'Password changed',
            'message' => 'Your account password was changed successfully.',
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
            'token' => $token,
        ]);
    }

    public function updateTwoFactor(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'two_factor_enabled' => ['required', 'boolean'],
        ]);

        $user->update([
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
        ]);

        ActivityLogger::log(
            'update_2fa',
            'company_account_settings',
            "Company owner {$user->email} updated 2FA setting to " . ($user->two_factor_enabled ? 'enabled' : 'disabled') . ".",
            $request
        );

        Notification::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'type' => 'system_alert',
            'title' => '2FA setting updated',
            'message' => $user->two_factor_enabled
                ? 'Two-factor authentication was enabled on your account.'
                : 'Two-factor authentication was disabled on your account.',
        ]);

        return response()->json([
            'message' => 'Two-factor setting updated successfully.',
            'two_factor_enabled' => (bool) $user->two_factor_enabled,
        ]);
    }
}

