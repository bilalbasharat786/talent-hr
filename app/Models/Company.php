<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'status',
        'trust_level',
        'rejection_reason',
    ];

    public function supervisors()
    {
        return $this->hasMany(Supervisor::class);
    }

    public function verificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class);
    }
    public function hrJobs()
{
    return $this->hasMany(HrJob::class);
}

}
