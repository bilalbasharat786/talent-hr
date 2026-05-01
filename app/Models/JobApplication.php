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
    ];

    public function job()
    {
        return $this->belongsTo(HrJob::class, 'job_id');
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }
}

