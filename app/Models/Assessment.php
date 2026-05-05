<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'hr_id',
        'title',
        'time_limit',
        'one_attempt_only',
        'auto_submit',
        'randomize_questions',
        'cooldown_days',
        'status',
    ];

    protected $casts = [
        'one_attempt_only' => 'boolean',
        'auto_submit' => 'boolean',
        'randomize_questions' => 'boolean',
    ];

    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function jobs()
    {
        return $this->hasMany(HrJob::class);
    }
}
