<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    protected $fillable = [
        'task_id',
        'candidate_id',
        'file_path',
        'status',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }
}
