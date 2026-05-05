<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'application_id',
        'title',
        'description',
        'instructions_file',
        'deadline',
        'submission_file',
        'status',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class);
    }
}
