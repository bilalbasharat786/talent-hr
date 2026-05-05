<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentSession extends Model
{
    protected $fillable = [
        'candidate_id',
        'assessment_id',
        'job_application_id',
        'session_token',
        'started_at',
        'expires_at',
        'status',
        'device_info',
        'ip_address',
        'warning_count',
        'violation_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'device_info' => 'array',
    ];

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function logs()
    {
        return $this->hasMany(AssessmentLog::class, 'session_id');
    }
}
