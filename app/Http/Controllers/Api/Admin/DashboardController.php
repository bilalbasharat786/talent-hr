<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\FraudLog;
use App\Models\HrJob;
use App\Models\Internship;
use App\Models\JobApplication;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'total_companies' => Company::count(),
                'verified_companies' => Company::where('status', 'approved')->count(),
                'pending_verifications' => Company::where('status', 'pending')->count()
                    + Internship::where('status', 'pending')->count(),
                'total_candidates' => User::where('role', 'candidate')->count(),
                'total_jobs' => HrJob::count(),
                'total_assessments' => 0,
                'fraud_alerts_count' => FraudLog::whereIn('status', ['open', 'flagged'])->count(),
            ],
            'recent_activities' => ActivityLog::with('user:id,name,email,role')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function reports()
    {
        return response()->json([
            'companies' => [
                'total' => Company::count(),
                'pending' => Company::where('status', 'pending')->count(),
                'approved' => Company::where('status', 'approved')->count(),
                'rejected' => Company::where('status', 'rejected')->count(),
                'by_trust_level' => Company::query()
                    ->selectRaw('trust_level, COUNT(*) as total')
                    ->groupBy('trust_level')
                    ->get(),
            ],
            'users' => [
                'total' => User::count(),
                'candidates' => User::where('role', 'candidate')->count(),
                'hr_users' => User::where('role', 'hr')->count(),
                'company_users' => User::where('role', 'company')->count(),
                'inactive_users' => User::where('status', 'inactive')->count(),
            ],
            'internships' => [
                'total' => Internship::count(),
                'pending' => Internship::where('status', 'pending')->count(),
                'verified' => Internship::where('status', 'verified')->count(),
                'partial' => Internship::where('status', 'partial')->count(),
                'rejected' => Internship::where('status', 'rejected')->count(),
            ],
            'hr_activity' => [
                'total_jobs' => HrJob::count(),
                'total_applications' => JobApplication::count(),
                'shortlisted_applications' => JobApplication::where('status', 'shortlisted')->count(),
                'rejected_applications' => JobApplication::where('status', 'rejected')->count(),
                'hired_applications' => JobApplication::where('status', 'hired')->count(),
            ],
            'fraud' => [
                'total' => FraudLog::count(),
                'open' => FraudLog::where('status', 'open')->count(),
                'flagged' => FraudLog::where('status', 'flagged')->count(),
                'resolved' => FraudLog::where('status', 'resolved')->count(),
                'fraud' => FraudLog::where('status', 'fraud')->count(),
                'by_type' => FraudLog::query()
                    ->selectRaw('type, COUNT(*) as total')
                    ->groupBy('type')
                    ->get(),
            ],
        ]);
    }
}

