<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
protected $fillable = [
    'name',
    'email',
    'phone',
    'status',
    'trust_level',
    'rejection_reason',
    'logo',
    'cover_image',
    'about',
    'industry',
    'company_size',
    'website',
    'office_locations',
    'working_hours',
];

protected $casts = [
    'office_locations' => 'array',
    'working_hours' => 'array',
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
public function users()
{
    return $this->hasMany(User::class);
}

public function owner()
{
    return $this->hasOne(User::class)->where('role', 'company');
}

public function hrUsers()
{
    return $this->hasMany(User::class)->where('role', 'hr');
}
public function notifications()
{
    return $this->hasMany(Notification::class);
}

}
