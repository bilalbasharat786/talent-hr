<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $fillable = [
        'application_id',
        'date',
        'time',
        'mode',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }
}

