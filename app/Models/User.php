<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'status',
        'company_id',
        'phone',
        'hr_type',
        'two_factor_enabled',
        'email_verification_code',
        'email_verification_expires_at',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
        'two_factor_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'email_verification_expires_at' => 'datetime',
            'two_factor_expires_at' => 'datetime',
        ];
    }
    public function hrJobs()
{
    return $this->hasMany(HrJob::class, 'hr_id');
}

public function jobApplications()
{
    return $this->hasMany(JobApplication::class, 'candidate_id');
}
public function company()
{
    return $this->belongsTo(Company::class);
}
public function notifications()
{
    return $this->hasMany(Notification::class);
}

public function assessments()
{
    return $this->hasMany(Assessment::class, 'hr_id');
}

public function assessmentSubmissions()
{
    return $this->hasMany(AssessmentSubmission::class, 'candidate_id');
}

}
