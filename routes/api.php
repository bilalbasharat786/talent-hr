<?php

use App\Http\Controllers\Api\Admin\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\CompanyVerificationController;
use App\Http\Controllers\Api\Admin\SupervisorVerificationController;
use App\Http\Controllers\Api\Admin\InternshipVerificationController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\Admin\HrMonitoringController;
use App\Http\Controllers\Api\Admin\FraudLogController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\SecureFileController;




Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'super_admin'])->group(function () {
        Route::get('/me', function () {
            return response()->json([
                'message' => 'Super admin route working.',
                'user' => request()->user(),
            ]);
            
        });
     

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/companies', [CompanyVerificationController::class, 'index']);
        Route::get('/companies/{company}', [CompanyVerificationController::class, 'show']);
        Route::post('/companies/{company}/approve', [CompanyVerificationController::class, 'approve']);
        Route::post('/companies/{company}/reject', [CompanyVerificationController::class, 'reject']);
        Route::get('/supervisors', [SupervisorVerificationController::class, 'index']);
        Route::get('/supervisors/{supervisor}', [SupervisorVerificationController::class, 'show']);
        Route::post('/supervisor/{supervisor}/approve', [SupervisorVerificationController::class, 'approve']);
        Route::post('/supervisor/{supervisor}/reject', [SupervisorVerificationController::class, 'reject']);
        Route::get('/internships', [InternshipVerificationController::class, 'index']);
        Route::get('/internships/{internship}', [InternshipVerificationController::class, 'show']);
        Route::post('/internships/{internship}/verify', [InternshipVerificationController::class, 'verify']);
        Route::post('/internships/{internship}/partial', [InternshipVerificationController::class, 'partial']);
        Route::post('/internships/{internship}/reject', [InternshipVerificationController::class, 'reject']);
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/users/{user}', [UserManagementController::class, 'show']);
        Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate']);
        Route::post('/users/{user}/activate', [UserManagementController::class, 'activate']);
        Route::get('/hr-monitoring', [HrMonitoringController::class, 'index']);
        Route::get('/hr-monitoring/{hr}', [HrMonitoringController::class, 'show']);
        Route::get('/fraud-logs', [FraudLogController::class, 'index']);
        Route::get('/fraud-logs/{fraudLog}', [FraudLogController::class, 'show']);
        Route::post('/fraud/{fraudLog}/flag', [FraudLogController::class, 'flag']);
        Route::post('/fraud/{fraudLog}/resolve', [FraudLogController::class, 'resolve']);
        Route::post('/fraud/{fraudLog}/mark-as-fraud', [FraudLogController::class, 'markAsFraud']);
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/reports', [DashboardController::class, 'reports']);
        Route::get('/files/company-document/{document}', [SecureFileController::class, 'companyDocument']);
        Route::get('/files/supervisor-selfie/{supervisor}', [SecureFileController::class, 'supervisorSelfie']);
        Route::get('/files/internship-certificate/{internship}', [SecureFileController::class, 'internshipCertificate']);







        Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    });
});