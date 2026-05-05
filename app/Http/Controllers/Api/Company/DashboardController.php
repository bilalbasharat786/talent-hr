<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\HrJob;
use App\Models\Internship;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $company = request()->user()->company;

        return response()->json([
            'company_status' => $company->status,
            'trust_level' => $company->trust_level,
            'hr_users_count' => User::where('company_id', $company->id)
                ->where('role', 'hr')
                ->count(),
            'jobs_overview' => [
                'draft' => HrJob::where('company_id', $company->id)->where('status', 'draft')->count(),
                'pending_approval' => HrJob::where('company_id', $company->id)->where('status', 'pending_approval')->count(),
                'live' => HrJob::where('company_id', $company->id)->where('status', 'live')->count(),
            ],
            'internship_overview' => [
                'total' => Internship::where('company_name', $company->name)->count(),
                'pending' => Internship::where('company_name', $company->name)->where('status', 'pending')->count(),
                'verified' => Internship::where('company_name', $company->name)->where('status', 'verified')->count(),
                'partial' => Internship::where('company_name', $company->name)->where('status', 'partial')->count(),
                'rejected' => Internship::where('company_name', $company->name)->where('status', 'rejected')->count(),
            ],
            'notifications' => [],
        ]);
    }
}

