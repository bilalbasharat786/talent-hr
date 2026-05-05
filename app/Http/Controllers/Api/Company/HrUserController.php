<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class HrUserController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $hrs = User::query()
            ->select('id', 'company_id', 'name', 'email', 'role', 'hr_type', 'status', 'created_at', 'updated_at')
            ->where('company_id', $company->id)
            ->where('role', 'hr')
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($hrs);
    }

    public function store(Request $request)
    {
        $company = $request->user()->company;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['hr'])],
            'hr_type' => ['nullable', Rule::in(['hr_manager', 'recruiter'])],
        ]);

        $hr = User::create([
            'company_id' => $company->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'hr',
            'hr_type' => $request->hr_type ?: 'recruiter',
            'status' => 'active',
        ]);

        ActivityLogger::log(
            'create',
            'company_hr_management',
            "Company {$company->name} created HR user {$hr->name}.",
            $request
        );

        Notification::create([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'type' => 'hr_activity',
            'title' => 'HR user added',
            'message' => "HR user {$hr->name} was added to your company.",
        ]);

        return response()->json([
            'message' => 'HR user created successfully.',
            'hr' => $hr,
        ], 201);
    }

    public function update(Request $request, User $hr)
    {
        $company = $request->user()->company;

        if ($hr->role !== 'hr' || $hr->company_id !== $company->id) {
            return response()->json([
                'message' => 'HR user not found for this company.',
            ], 404);
        }

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($hr->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'hr_type' => ['nullable', Rule::in(['hr_manager', 'recruiter'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $data = $request->only([
            'name',
            'email',
            'hr_type',
            'status',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $hr->update($data);

        ActivityLogger::log(
            'update',
            'company_hr_management',
            "Company {$company->name} updated HR user {$hr->name}.",
            $request
        );

        Notification::create([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'type' => 'hr_activity',
            'title' => 'HR user updated',
            'message' => "HR user {$hr->name} was updated.",
        ]);

        return response()->json([
            'message' => 'HR user updated successfully.',
            'hr' => $hr->fresh(),
        ]);
    }

    public function deactivate(Request $request, User $hr)
    {
        $company = $request->user()->company;

        if ($hr->role !== 'hr' || $hr->company_id !== $company->id) {
            return response()->json([
                'message' => 'HR user not found for this company.',
            ], 404);
        }

        $hr->update([
            'status' => 'inactive',
        ]);

        $hr->tokens()->delete();

        ActivityLogger::log(
            'deactivate',
            'company_hr_management',
            "Company {$company->name} deactivated HR user {$hr->name}.",
            $request
        );

        Notification::create([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'type' => 'hr_activity',
            'title' => 'HR user deactivated',
            'message' => "HR user {$hr->name} was deactivated.",
        ]);

        return response()->json([
            'message' => 'HR user deactivated successfully.',
            'hr' => $hr->fresh(),
        ]);
    }
}


