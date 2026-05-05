<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentSubmission extends Model
{
    protected $fillable = [
        'assessment_id',
        'candidate_id',
        'score',
        'status',
        'started_at',
        'submitted_at',
        'cheating_flag',
        'plagiarism_report',
        'answers_payload',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'answers_payload' => 'array',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }
}
