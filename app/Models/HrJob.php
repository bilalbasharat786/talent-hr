<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrJob extends Model
{
    protected $fillable = [
        'hr_id',
        'company_id',
        'title',
        'status',
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
}

