<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'candidate_id',
        'status',
        'rejection_reason',
        'skill_match_percentage',
        'experience_verification_status',
        'plagiarism_report',
        'anti_cheat_logs',
        'portfolio_links',
        'cooldown_until',
    ];

    protected $casts = [
        'portfolio_links' => 'array',
        'cooldown_until' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(HrJob::class, 'job_id');
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function task()
    {
        return $this->hasOne(Task::class, 'application_id');
    }

    public function interview()
    {
        return $this->hasOne(Interview::class, 'application_id');
    }
}


