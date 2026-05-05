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
use App\Http\Controllers\Api\Company\AuthController as CompanyAuthController;
use App\Http\Controllers\Api\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Api\Company\ProfileController as CompanyProfileController;
use App\Http\Controllers\Api\Company\DocumentController as CompanyDocumentController;
use App\Http\Controllers\Api\Company\SupervisorController as CompanySupervisorController;
use App\Http\Controllers\Api\Company\HrUserController as CompanyHrUserController;
use App\Http\Controllers\Api\Company\HiringOverviewController as CompanyHiringOverviewController;
use App\Http\Controllers\Api\Company\NotificationController as CompanyNotificationController;
use App\Http\Controllers\Api\Company\AccountSettingsController as CompanyAccountSettingsController;




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
Route::prefix('company')->group(function () {
    Route::post('/register', [CompanyAuthController::class, 'register']);
    Route::post('/login', [CompanyAuthController::class, 'login']);
    Route::post('/verify-email', [CompanyAuthController::class, 'verifyEmail']);
    Route::post('/resend-verification-code', [CompanyAuthController::class, 'resendVerificationCode']);
    Route::post('/verify-2fa', [CompanyAuthController::class, 'verifyTwoFactor']);
 

        Route::middleware(['auth:sanctum', 'company_owner'])->group(function () {
        Route::get('/me', [CompanyAuthController::class, 'me']);
        Route::post('/logout', [CompanyAuthController::class, 'logout']);
        Route::get('/dashboard', [CompanyDashboardController::class, 'index']);
        Route::get('/profile', [CompanyProfileController::class, 'show']);
        Route::put('/profile', [CompanyProfileController::class, 'update']);
        Route::post('/documents', [CompanyDocumentController::class, 'store']);
        Route::post('/supervisor', [CompanySupervisorController::class, 'store']);
        Route::get('/hr', [CompanyHrUserController::class, 'index']);
        Route::post('/hr', [CompanyHrUserController::class, 'store']);
        Route::put('/hr/{hr}', [CompanyHrUserController::class, 'update']);
        Route::post('/hr/{hr}/deactivate', [CompanyHrUserController::class, 'deactivate']);
        Route::get('/jobs-overview', [CompanyHiringOverviewController::class, 'index']);
        Route::get('/notifications', [CompanyNotificationController::class, 'index']);
        Route::post('/notifications/read-all', [CompanyNotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/{notification}/read', [CompanyNotificationController::class, 'markAsRead']);
        Route::get('/account-settings', [CompanyAccountSettingsController::class, 'show']);
        Route::post('/account-settings/change-password', [CompanyAccountSettingsController::class, 'changePassword']);
        Route::post('/account-settings/two-factor', [CompanyAccountSettingsController::class, 'updateTwoFactor']);



    });
});
