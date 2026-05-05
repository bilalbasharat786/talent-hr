<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrJob extends Model
{
    protected $fillable = [
        'hr_id',
        'company_id',
        'title',
        'type',
        'work_mode',
        'location',
        'skills',
        'experience_level',
        'education',
        'description',
        'candidates_required',
        'hiring_urgency',
        'assessment_id',
        'status',
    ];

    protected $casts = [
        'skills' => 'array',
    ];

    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}


